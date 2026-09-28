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
        $pending = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_PENDING)
            ->count();

        $inProgress = Order::where('store_id', $storeId)
            ->whereIn('status', [
                Order::STATUS_PAYMENT_VERIFIED,
                Order::STATUS_TRANSFERRED,
                Order::STATUS_FULFILLING,
            ])
            ->count();

        $deliveredToday = Order::where('store_id', $storeId)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereDate('updated_at', $today)
            ->count();

        // ── Total orders today (all statuses) ─────────────────────────────────
        $totalToday = Order::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->count();

        return [
            Stat::make('Pending Orders', $pending)
                ->description('Awaiting payment verification')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-inbox-stack'),

            Stat::make('In Progress', $inProgress)
                ->description('Verified, transferred, or being fulfilled by Huashu')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($inProgress > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-truck'),

            Stat::make('Delivered Today', $deliveredToday)
                ->description('Orders completed today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('New Orders Today', $totalToday)
                ->description('All orders placed today')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('gray')
                ->icon('heroicon-o-shopping-bag'),
        ];
    }
}
