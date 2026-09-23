<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class OrdersByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Orders by Status';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '220px';

    protected function getData(): array
    {
        $statusLabels = [
            Order::STATUS_PENDING           => 'Pending',
            Order::STATUS_PAYMENT_VERIFIED  => 'Payment Verified',
            Order::STATUS_TRANSFERRED       => 'Transferred',
            Order::STATUS_FULFILLING        => 'Fulfilling',
            Order::STATUS_DELIVERED         => 'Delivered',
            Order::STATUS_CANCELLED         => 'Cancelled',
        ];

        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data   = [];

        foreach ($statusLabels as $key => $label) {
            $labels[] = $label;
            $data[]   = (int) ($counts[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $data,
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.7)',   // pending      — amber
                        'rgba(59, 130, 246, 0.7)',   // verified     — blue
                        'rgba(168, 85, 247, 0.7)',   // transferred  — purple
                        'rgba(234, 179, 8, 0.7)',    // fulfilling   — yellow
                        'rgba(34, 197, 94, 0.7)',    // delivered    — green
                        'rgba(239, 68, 68, 0.7)',    // cancelled    — red
                    ],
                    'borderColor' => [
                        'rgb(251, 191, 36)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(234, 179, 8)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
