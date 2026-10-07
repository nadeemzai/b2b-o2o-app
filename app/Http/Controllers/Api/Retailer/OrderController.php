<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderLocationService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService         $orderService,
        private readonly OrderLocationService $locationService,
    ) {
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/orders
    // ──────────────────────────────────────────────

    /**
     * Paginated order history for the authenticated retailer.
     *
     * Query params:
     *   - status   (string, optional)
     *   - per_page (int, default 15)
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $retailer = $request->user()->retailerProfile;

        $orders = Order::forRetailer($retailer->id)
            ->when($request->query('status'), fn ($q, $s) => $q->status($s))
            ->with(['items.product', 'store'])
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page', 15));

        return OrderResource::collection($orders);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders
    // ──────────────────────────────────────────────

    /**
     * Place a new order.
     *
     * Body:
     * {
     *   "items": [
     *     { "product_id": 1, "qty": 3 },
     *     { "product_id": 4, "qty": 1 }
     *   ],
     *   "notes": "optional delivery note"
     * }
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $this->authorize('placeOrder', Order::class);

        $validated = $request->validated();
        $retailer  = $request->user()->retailerProfile;

        $items = $request->mergedItems();
        $order = $this->orderService->placeOrder($retailer, $items);

        // Capture geolocation + device metadata (non-blocking on failure).
        $locationData = $this->locationService->collect($request);

        if (isset($validated['notes'])) {
            $locationData['notes'] = $validated['notes'];
        }

        $order->update($locationData);

        return response()->json(['data' => new OrderResource($order)], 201);
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/orders/{order}
    // ──────────────────────────────────────────────

    /**
     * Return a single order with full detail.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('viewOrder', $order);

        $order->load(['items.product', 'statusHistory.changedBy', 'store']);

        return response()->json(['data' => new OrderResource($order)]);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders/{order}/cancel
    // ──────────────────────────────────────────────

    /**
     * Cancel an order (only allowed while still pending).
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancelOrder', $order);

        $order = $this->orderService->cancelOrder($order, $request->user()->id);

        return response()->json(['data' => new OrderResource($order)]);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders/{order}/payment-proof
    // ──────────────────────────────────────────────

    /**
     * Upload a payment proof image for a pending order.
     *
     * Body (multipart/form-data):
     *   proof_file — image, mimes: jpg/jpeg/png/webp, max 5 MB
     *
     * Returns the updated order with payment_proof_path set.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function uploadPaymentProof(Request $request, Order $order): JsonResponse
    {
        // Verify the order belongs to the authenticated retailer
        $retailer = $request->user()->retailerProfile;
        abort_unless(
            $order->retailer_id === $retailer->id,
            403,
            'This order does not belong to you.'
        );

        abort_unless(
            $order->status === Order::STATUS_PENDING,
            422,
            'Payment proof can only be uploaded for pending orders.'
        );

        $request->validate([
            'proof_file' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('proof_file')->store('payment-proofs', 'public');

        $order->update(['payment_proof_path' => $path]);

        return response()->json([
            'message'            => 'Payment proof uploaded successfully.',
            'payment_proof_path' => $path,
            'data'               => new OrderResource($order->fresh()),
        ]);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders/{order}/reorder
    // ──────────────────────────────────────────────

    /**
     * Re-add a past order's active items to the cart and return them.
     *
     * Response shape:
     * {
     *   "added":   3,
     *   "skipped": 1,
     *   "items": [
     *     { "product_id": 1, "qty": 5, "price_pkr": 450.00, "name": "..." }
     *   ]
     * }
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function reorder(Request $request, Order $order): JsonResponse
    {
        $retailer = $request->user()->retailerProfile;

        abort_unless(
            $order->retailer_id === $retailer->id,
            403,
            'This order does not belong to you.'
        );

        $order->load('items.product');

        /** @var PricingService $pricing */
        $pricing = app(PricingService::class);

        $added   = [];
        $skipped = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active) {
                $skipped[] = $item->product_id;
                continue;
            }

            $product->loadMissing('category');
            $price = $pricing->retailerPrice($product);

            if (! $price) {
                $skipped[] = $product->id;
                continue;
            }

            $moq     = max(1, (int) $product->moq);
            $qty     = max($moq, (int) $item->qty);
            $added[] = [
                'product_id' => $product->id,
                'qty'        => $qty,
                'price_pkr'  => $price,
                'name_en'    => $product->name_en,
                'unit'       => $product->unit,
                'moq'        => $moq,
            ];
        }

        return response()->json([
            'added'   => count($added),
            'skipped' => count($skipped),
            'items'   => $added,
        ]);
    }
}
