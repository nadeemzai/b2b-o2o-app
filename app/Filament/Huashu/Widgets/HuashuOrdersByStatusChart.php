<?php

namespace App\Filament\Huashu\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class HuashuOrdersByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Pipeline (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '220px';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        // Build per-day counts for each Huashu-relevant status
        $statuses = [
            Order::STATUS_TRANSFERRED => ['label' => 'Transferred',          'color' => 'rgba(168, 85, 247, 0.7)',  'border' => 'rgb(168, 85, 247)'],
            Order::STATUS_FULFILLING  => ['label' => 'Fulfilling',           'color' => 'rgba(59, 130, 246, 0.7)',  'border' => 'rgb(59, 130, 246)'],
            Order::STATUS_DISPATCHED  => ['label' => 'Dispatched to Store',  'color' => 'rgba(251, 146, 60, 0.7)',  'border' => 'rgb(251, 146, 60)'],
            Order::STATUS_DELIVERED   => ['label' => 'Delivered',            'color' => 'rgba(34, 197, 94, 0.7)',   'border' => 'rgb(34, 197, 94)'],
        ];

        // Count orders created in Huashu scope per day (by created_at) grouped by status
        $rawCounts = Order::query()
            ->forHuashu()
            ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
            ->selectRaw("DATE(created_at) as day, status, COUNT(*) as total")
            ->groupBy('day', 'status')
            ->get()
            ->groupBy('status');

        $labels   = $days->map(fn ($d) => $d->format('d M'))->values()->toArray();
        $datasets = [];

        foreach ($statuses as $statusKey => $meta) {
            $statusRows = $rawCounts->get($statusKey, collect())->keyBy('day');
            $data = $days->map(fn ($d) => (int) ($statusRows[$d->toDateString()]->total ?? 0))->values()->toArray();

            $datasets[] = [
                'label'           => $meta['label'],
                'data'            => $data,
                'backgroundColor' => $meta['color'],
                'borderColor'     => $meta['border'],
                'borderWidth'     => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => false, 'ticks' => ['maxTicksLimit' => 10]],
                'y' => ['stacked' => false, 'beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
