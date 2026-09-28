<?php

namespace App\Filament\Huashu\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HuashuOrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $monthStart = Carbon::now()->startOfMonth();

        // Orders newly transferred to Huashu — awaiting action
        $awaitingFulfillment = Order::where('status', Order::STATUS_TRANSFERRED)->count();

        // Currently being processed / sourced
        $fulfilling = Order::where('status', Order::STATUS_FULFILLING)->count();

        // Dispatched to OZ Store — pending retailer delivery confirmation
        $dispatched = Order::where('status', Order::STATUS_DISPATCHED)->count();

        // Delivered this calendar month
        $deliveredThisMonth = Order::where('status', Order::STATUS_DELIVERED)
            ->where('updated_at', '>=', $monthStart)
            ->count();

        // All-time delivered count
        $deliveredTotal = Order::where('status', Order::STATUS_DELIVERED)->count();

        // Revenue processed this month (sum of order totals for delivered orders this month)
        $revenueThisMonth = Order::where('status', Order::STATUS_DELIVERED)
            ->where('updated_at', '>=', $monthStart)
            ->sum('total_pkr');

        // Active pipeline (transferred + fulfilling + dispatched)
        $activePipeline = $awaitingFulfillment + $fulfilling + $dispatched;

        return [
            Stat::make('Awaiting Fulfillment', $awaitingFulfillment)
                ->description('Transferred orders — action required')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($awaitingFulfillment > 0 ? 'warning' : 'success'),

            Stat::make('Currently Fulfilling', $fulfilling)
                ->description('Being sourced / in transit from China')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($fulfilling > 0 ? 'info' : 'gray'),

            Stat::make('Dispatched to OZ Store', $dispatched)
                ->description('Sent — awaiting delivery confirmation')
                ->descriptionIcon('heroicon-m-truck')
                ->color($dispatched > 0 ? 'primary' : 'gray'),

            Stat::make('Delivered This Month', $deliveredThisMonth)
                ->description('Completed — ' . Carbon::now()->format('F Y') . ' (' . $deliveredTotal . ' all-time)')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Revenue Processed', 'PKR ' . number_format($revenueThisMonth, 0))
                ->description('Delivered order value — ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Pipeline', $activePipeline)
                ->description('Orders currently in Huashu workflow')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($activePipeline > 0 ? 'info' : 'gray'),
        ];
    }
}
