<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Store staff no longer transition order status or mark delivery — the Huashu
// wholesale flow (OZ admin verifies payment + transfers, Huashu fulfills and
// delivers) owns that lifecycle now. This controller is read-only visibility
// into orders that draw on the store's stock.
class StoreOrderController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/store/orders
    // ──────────────────────────────────────────────

    /**
     * Return orders for the staff member's store.
     *
     * Query params:
     *   - status   (string, optional) — default: undelivered/uncancelled orders only
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
                fn ($q)     => $q->active()
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
}
