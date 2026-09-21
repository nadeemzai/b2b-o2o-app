<?php

namespace App\Filament\Store\Widgets;

use App\Filament\Store\StoreStaffContext;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StoreOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    /**
     * Poll every 30 seconds so the dashboard stays fresh
     * without a manual page refresh.
     */
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $storeId = StoreStaffContext::storeId();
        $today   = Carbon::today();

        // ── Counts ────────────────────────────────────────────────────────────
        $pending  = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_PENDING)
            ->count();

        $preparing = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_PREPARING)
            ->count();

        $ready = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_READY_FOR_DELIVERY)
            ->count();

        $deliveredToday = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereDate('updated_at', $today)
            ->count();

        // ── Cash collected today (COD orders only) ────────────────────────────
        $cashToday = (float) Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_DELIVERED)
            ->where('payment_method', 'cod')
            ->whereDate('updated_at', $today)
            ->sum('collected_pkr');

        // ── Total orders today (all statuses) ─────────────────────────────────
        $totalToday = Order::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->count();

        return [
            Stat::make('Pending Orders', $pending)
                ->description('Awaiting preparation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-inbox-stack'),

            Stat::make('In Preparation', $preparing)
                ->description('Being packed right now')
                ->descriptionIcon('heroicon-m-fire')
                ->color($preparing > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-fire'),

            Stat::make('Ready for Delivery', $ready)
                ->description('Waiting for rider pickup')
                ->descriptionIcon('heroicon-m-cube')
                ->color($ready > 0 ? 'primary' : 'gray')
                ->icon('heroicon-o-cube'),

            Stat::make('Delivered Today', $deliveredToday)
                ->description('Orders completed today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Cash Collected Today', 'PKR ' . number_format($cashToday, 0))
                ->description('COD payments received today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('New Orders Today', $totalToday)
                ->description('All orders placed today')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('gray')
                ->icon('heroicon-o-shopping-bag'),
        ];
    }
}
