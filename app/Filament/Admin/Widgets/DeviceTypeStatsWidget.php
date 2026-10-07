<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;

class DeviceTypeStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    /** Received from the OzDateRangeFilter widget. */
    public ?string $ozFrom = null;
    public ?string $ozTo   = null;

    #[On('oz-date-range-updated')]
    public function updateDateRange(string $from, string $to): void
    {
        $this->ozFrom = $from;
        $this->ozTo   = $to;
    }

    protected function getStats(): array
    {
        // Graceful fallback: migration hasn't run yet → show placeholder stats.
        if (! Schema::hasColumn('orders', 'device_type')) {
            return [
                Stat::make('Mobile App Orders', '—')
                    ->description('Run migration to enable')
                    ->color('gray'),
                Stat::make('Web Orders', '—')
                    ->description('Run migration to enable')
                    ->color('gray'),
                Stat::make('Unknown Device', '—')
                    ->description('Run migration to enable')
                    ->color('gray'),
            ];
        }

        $query = Order::query()
            ->when($this->ozFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->ozFrom))
            ->when($this->ozTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->ozTo));

        $total  = (clone $query)->count();
        $mobile = (clone $query)->where('device_type', 'mobile_app')->count();
        $web    = (clone $query)->where('device_type', 'web')->count();
        $unknown = (clone $query)->where('device_type', 'unknown')->count();

        $pct = fn (int $n): string => $total > 0
            ? round(($n / $total) * 100, 1) . '% of orders'
            : '0% of orders';

        $period = $this->getPeriodLabel();

        return [
            Stat::make('Mobile App Orders', number_format($mobile))
                ->description($pct($mobile) . ($period ? " · {$period}" : ''))
                ->color('warning')
                ->icon('heroicon-o-device-phone-mobile'),

            Stat::make('Web Orders', number_format($web))
                ->description($pct($web) . ($period ? " · {$period}" : ''))
                ->color('info')
                ->icon('heroicon-o-computer-desktop'),

            Stat::make('Unknown Device', number_format($unknown))
                ->description($pct($unknown) . ($period ? " · {$period}" : ''))
                ->color('gray')
                ->icon('heroicon-o-question-mark-circle'),
        ];
    }

    private function getPeriodLabel(): string
    {
        if ($this->ozFrom && $this->ozTo) {
            return date('d M', strtotime($this->ozFrom)) . ' – ' . date('d M Y', strtotime($this->ozTo));
        }
        return '';
    }
}
