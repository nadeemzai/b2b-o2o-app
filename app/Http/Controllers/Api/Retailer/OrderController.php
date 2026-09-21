<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
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
     *   - status (string, optional)
     *   - per_page (int, default 15)
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
     * Policy: retailer must be KYC-approved.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('placeOrder', Order::class);

        $validated = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:1000'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $retailer = $request->user()->retailerProfile;

        $order = $this->orderService->placeOrder($retailer, $validated['items']);

        if (isset($validated['notes'])) {
            $order->update(['notes' => $validated['notes']]);
        }

        return response()->json(['data' => new OrderResource($order)], 201);
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/orders/{order}
    // ──────────────────────────────────────────────

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('viewOrder', $order);

        $order->load(['items.product', 'statusHistory.changedBy', 'store']);

        return response()->json(['data' => new OrderResource($order)]);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders/{order}/cancel
    // ──────────────────────────────────────────────

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancelOrder', $order);

        $order = $this->orderService->cancelOrder($order, $request->user()->id);

        return response()->json(['data' => new OrderResource($order)]);
    }
}
