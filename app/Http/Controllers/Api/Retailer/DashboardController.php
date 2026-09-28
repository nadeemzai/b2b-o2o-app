<?php

namespace App\Http\Controllers\Api\Retailer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // ──────────────────────────────────────────────
    // GET /api/retailer/dashboard
    // ──────────────────────────────────────────────

    /**
     * Order statistics + 5 most-recent orders for the authenticated retailer.
     *
     * Response shape:
     * {
     *   "stats": {
     *     "pending":     3,
     *     "in_progress": 1,
     *     "delivered":   12,
     *     "total":       16
     *   },
     *   "recent_orders": [ ...OrderResource ]
     * }
     *
     * Middleware: auth:sanctum, role:retailer
     */
    public function __invoke(Request $request): JsonResponse
    {
        $retailer   = $request->user()->retailerProfile;
        $retailerId = $retailer->id;

        $base = Order::where('retailer_id', $retailerId);

        $stats = [
            'pending'     => (clone $base)->where('status', Order::STATUS_PENDING)->count(),
            'in_progress' => (clone $base)->whereIn('status', [
                                'payment_verified',
                                'transferred',
                                'fulfilling',
                            ])->count(),
            'delivered'   => (clone $base)->where('status', 'delivered')->count(),
            'total'       => (clone $base)->count(),
        ];

        $recentOrders = Order::where('retailer_id', $retailerId)
            ->with(['items.product', 'store'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'stats'         => $stats,
            'recent_orders' => OrderResource::collection($recentOrders),
        ]);
    }
}
