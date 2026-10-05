<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Retailer;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class OrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    #[On('oz-date-range-updated')]
    public function updateDateRange(string $from, string $to): void
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
    }

    protected function getStats(): array
    {
        // ── Period-aware base query ──────────────────────────────────────
        $inPeriod = fn () => Order::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        $periodLabel = $this->getPeriodLabel();

        // Period-scoped counts
        $ordersInPeriod = $inPeriod()->count();

        $commissionInPeriod = Order::query()
            ->where('status', Order::STATUS_DELIVERED)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('updated_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('updated_at', '<=', $this->dateTo))
            ->sum('oz_commission_pkr');

        // ── Always-current operational queues ───────────────────────────
        $pendingKyc            = Retailer::where('kyc_status', 'pending')->count();
        $awaitingVerification  = Order::where('status', Order::STATUS_PENDING)->count();
        $readyToTransfer       = Order::where('status', Order::STATUS_PAYMENT_VERIFIED)->count();
        $activeRetailers       = Retailer::where('kyc_status', 'approved')->count();

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

            Stat::make('Orders — ' . $periodLabel, $ordersInPeriod)
                ->description('New orders placed in selected period')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Commission — ' . $periodLabel, 'PKR ' . number_format($commissionInPeriod, 0))
                ->description('From delivered orders in selected period')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Retailers', $activeRetailers)
                ->description('KYC approved accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }

    private function getPeriodLabel(): string
    {
        if ($this->dateFrom === '' && $this->dateTo === '') {
            return 'All Time';
        }

        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('d M Y') : '—';
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->format('d M Y')   : '—';

        return $from === $to ? $from : "{$from} → {$to}";
    }
}
