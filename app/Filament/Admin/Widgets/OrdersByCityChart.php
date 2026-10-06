<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class OrdersByCityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

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

    public function getHeading(): string
    {
        $from  = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('d M') : '—';
        $to    = $this->dateTo   ? Carbon::parse($this->dateTo)->format('d M Y') : '—';
        return "Orders by City — {$from} to {$to}";
    }

    protected function getData(): array
    {
        $rows = Order::query()
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(order_city), ''), 'Unknown') AS city"),
                DB::raw('COUNT(*) AS total')
            )
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(15)   // show top 15 cities
            ->get();

        $palette = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#14b8a6',
            '#6366f1', '#eab308', '#d946ef', '#0ea5e9', '#22c55e',
        ];

        $labels = $rows->pluck('city')->toArray();
        $data   = $rows->pluck('total')->toArray();
        $colors = array_slice($palette, 0, count($labels));

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'borderColor'     => $colors,
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',   // horizontal bar — easier to read city names
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
