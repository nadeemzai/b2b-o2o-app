<?php

namespace App\Filament\Huashu\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class HuashuOrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        // Default: this month — synced with filter widget's default preset
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    #[On('huashu-date-range-updated')]
    public function updateDateRange(string $from, string $to): void
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
    }

    protected function getStats(): array
    {
        $hasRange = $this->dateFrom !== '' || $this->dateTo !== '';

        // Base query with optional date filter on created_at
        $base = fn () => Order::query()
            ->forHuashu()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        // For delivered/revenue: filter by updated_at (the date they were marked delivered)
        $delivered = fn () => Order::query()
            ->forHuashu()
            ->where('status', Order::STATUS_DELIVERED)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('updated_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('updated_at', '<=', $this->dateTo));

        $awaitingFulfillment = $base()->where('status', Order::STATUS_TRANSFERRED)->count();
        $fulfilling          = $base()->where('status', Order::STATUS_FULFILLING)->count();
        $dispatched          = $base()->where('status', Order::STATUS_DISPATCHED)->count();
        $deliveredCount      = $delivered()->count();
        $revenue             = $delivered()->sum('total_pkr');
        $activePipeline      = $awaitingFulfillment + $fulfilling + $dispatched;

        $periodLabel = $hasRange
            ? ($this->dateFrom === $this->dateTo && $this->dateFrom !== ''
                ? Carbon::parse($this->dateFrom)->format('d M Y')
                : trim(
                    ($this->dateFrom ? Carbon::parse($this->dateFrom)->format('d M') : '') .
                    ' – ' .
                    ($this->dateTo ? Carbon::parse($this->dateTo)->format('d M Y') : '')
                , ' –'))
            : 'All Time';

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

            Stat::make('Delivered', $deliveredCount)
                ->description('Completed — ' . $periodLabel)
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Revenue Processed', 'PKR ' . number_format($revenue, 0))
                ->description('Delivered order value — ' . $periodLabel)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Pipeline', $activePipeline)
                ->description('Orders currently in Huashu workflow')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($activePipeline > 0 ? 'info' : 'gray'),
        ];
    }
}
