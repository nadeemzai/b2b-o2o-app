<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Retailer;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $monthStart = Carbon::now()->startOfMonth();

        $commissionThisMonth = Order::query()
            ->where('status', Order::STATUS_DELIVERED)
            ->where('updated_at', '>=', $monthStart)
            ->sum('oz_commission_pkr');

        $pendingKyc = Retailer::where('kyc_status', 'pending')->count();
        $awaitingVerification = Order::where('status', Order::STATUS_PENDING)->count();
        $readyToTransfer = Order::where('status', Order::STATUS_PAYMENT_VERIFIED)->count();
        $activeRetailers = Retailer::where('kyc_status', 'approved')->count();

        return [
            Stat::make('Pending KYC', $pendingKyc)
                ->description('Retailers awaiting KYC review')
                ->descriptionIcon('heroicon-m-identification')
                ->color($pendingKyc > 0 ? 'warning' : 'success'),

            Stat::make('Awaiting Payment Verification', $awaitingVerification)
                ->description('Orders pending payment review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($awaitingVerification > 0 ? 'warning' : 'success'),

            Stat::make('Ready to Transfer', $readyToTransfer)
                ->description('Verified — not yet sent to Huashu')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color($readyToTransfer > 0 ? 'info' : 'gray'),

            Stat::make('Commission This Month', 'PKR ' . number_format($commissionThisMonth, 0))
                ->description('From delivered orders — ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Retailers', $activeRetailers)
                ->description('KYC approved accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
