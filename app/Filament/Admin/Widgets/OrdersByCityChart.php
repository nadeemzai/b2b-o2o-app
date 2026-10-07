<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;

class OrdersByCityChart extends ChartWidget
{
    protected static ?string $heading = 'Orders by City';

    protected static ?int $sort = 3;

    /** Received from the OzDateRangeFilter widget. */
    public ?string $ozFrom = null;
    public ?string $ozTo   = null;

    #[On('oz-date-range-updated')]
    public function updateDateRange(string $from, string $to): void
    {
        $this->ozFrom = $from;
        $this->ozTo   = $to;
    }

    protected function getData(): array
    {
        // Graceful fallback: migration hasn't run yet → return empty chart.
        if (! Schema::hasColumn('orders', 'order_city')) {
            return [
                'datasets' => [
                    [
                        'label'           => 'Orders',
                        'data'            => [],
                        'backgroundColor' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $query = Order::query()
            ->selectRaw("COALESCE(NULLIF(TRIM(order_city), ''), 'Unknown') AS city, COUNT(*) AS total")
            ->when($this->ozFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->ozFrom))
            ->when($this->ozTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->ozTo))
            ->groupByRaw("COALESCE(NULLIF(TRIM(order_city), ''), 'Unknown')")
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $colors = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#f97316', '#14b8a6', '#e11d48', '#84cc16',
            '#6366f1', '#ec4899', '#0ea5e9', '#a16207', '#7c3aed',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $query->pluck('total')->toArray(),
                    'backgroundColor' => collect($query)->keys()
                        ->map(fn ($i) => $colors[$i % count($colors)])
                        ->toArray(),
                ],
            ],
            'labels' => $query->pluck('city')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'ticks' => ['stepSize' => 1],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
