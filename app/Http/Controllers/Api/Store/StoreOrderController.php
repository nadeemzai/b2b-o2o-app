<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreOrderController extends Controller
{
    public function __construct(
        private readonly OrderService                $orderService,
        private readonly OrderStatusTransitionService $transitionService,
    ) {
    }

    // ──────────────────────────────────────────────
    // GET /api/store/orders
    // ──────────────────────────────────────────────

    /**
     * Return the order queue for the staff member's store.
     *
     * Query params:
     *   - status   (string, optional) — default: active orders only
     *   - per_page (int, default 20)
     *
     * Middleware: auth:sanctum, role:store_staff
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $storeId = $request->user()->activeStoreStaff->store_id;

        $orders = Order::forStore($storeId)
            ->when(
                $request->query('status'),
                fn ($q, $s) => $q->status($s),
                fn ($q)     => $q->active()   // pending + preparing + ready_for_delivery
            )
            ->with(['retailer.user', 'items.product'])
            ->orderBy('created_at')
            ->paginate($request->query('per_page', 20));

        return OrderResource::collection($orders);
    }

    // ──────────────────────────────────────────────
    // GET /api/store/orders/{order}
    // ──────────────────────────────────────────────

    /**
     * Return a single order with full detail.
     *
     * Middleware: auth:sanctum, role:store_staff
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('manageOrder', $order);

        $order->load(['retailer.user', 'items.product', 'statusHistory.changedBy']);

        return response()->json(['data' => new OrderResource($order)]);
    }

    // ──────────────────────────────────────────────
    // POST /api/store/orders/{order}/status
    // ──────────────────────────────────────────────

    /**
     * Transition an order's status (pending → preparing → ready_for_delivery).
     *
     * Body: { "status": "preparing", "note": "optional" }
     *
     * Middleware: auth:sanctum, role:store_staff
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('manageOrder', $order);

        $validated = $request->validated();

        $order = $this->transitionService->transition(
            $order,
            $validated['status'],
            $request->user()->id,
            $validated['note'] ?? null,
        );

        return response()->json(['data' => new OrderResource($order)]);
    }

    // ──────────────────────────────────────────────
    // POST /api/store/orders/{order}/deliver
    // ──────────────────────────────────────────────

    /**
     * Mark as delivered and record COD collection.
     * Only riders and managers may call this.
     *
     * Body: { "collected_pkr": 1250.00 }
     *
     * Middleware: auth:sanctum, role:store_staff
     */
    public function deliver(DeliverOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('deliverOrder', $order);

        abort_unless(
            $order->status === Order::STATUS_READY_FOR_DELIVERY,
            422,
            "Order must be in 'ready_for_delivery' status before marking as delivered."
        );

        $validated = $request->validated();

        $order = $this->orderService->deliverOrder(
            $order,
            (float) $validated['collected_pkr'],
            $request->user()->id,
        );

        return response()->json(['data' => new OrderResource($order)]);
    }
}
