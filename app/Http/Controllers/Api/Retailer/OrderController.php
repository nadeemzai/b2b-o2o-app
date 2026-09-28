<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
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
     * Policy: retailer must be KYC-approved to place orders.
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $this->authorize('placeOrder', Order::class);

        $validated = $request->validated();
        $retailer  = $request->user()->retailerProfile;

        // PlaceOrderRequest::mergedItems() de-duplicates by product_id (summing qty)
        $items = $request->mergedItems();

        $order = $this->orderService->placeOrder($retailer, $items);

        if (isset($validated['notes'])) {
            $order->update(['notes' => $validated['notes']]);
        }

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
}
