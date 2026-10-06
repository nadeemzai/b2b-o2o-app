<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class DeviceTypeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

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
        $from = $this->dateFrom;
        $to   = $this->dateTo;

        $baseQuery = fn () => Order::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to));

        $total     = (clone $baseQuery())->count();
        $mobile    = (clone $baseQuery())->where('device_type', 'mobile_app')->count();
        $web       = (clone $baseQuery())->where('device_type', 'web')->count();
        $unknown   = (clone $baseQuery())->where('device_type', 'unknown')->count();

        $mobilePercent = $total > 0 ? round($mobile / $total * 100, 1) : 0;
        $webPercent    = $total > 0 ? round($web    / $total * 100, 1) : 0;

        $label = $this->getPeriodLabel();

        return [
            Stat::make("📱 Mobile App Orders ({$label})", $mobile)
                ->description("{$mobilePercent}% of all orders")
                ->color('warning'),

            Stat::make("🌐 Web Orders ({$label})", $web)
                ->description("{$webPercent}% of all orders")
                ->color('info'),

            Stat::make("❓ Unknown Device ({$label})", $unknown)
                ->description('No device header / UA detected')
                ->color('gray'),
        ];
    }

    private function getPeriodLabel(): string
    {
        if ($this->dateFrom && $this->dateTo) {
            return Carbon::parse($this->dateFrom)->format('d M') . ' – ' . Carbon::parse($this->dateTo)->format('d M Y');
        }
        return 'All Time';
    }
}
