<?php

namespace App\Filament\Huashu\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class HuashuOrdersByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '220px';

    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        // Default: this month — synced with filter widget's default preset
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function getHeading(): string
    {
        if ($this->dateFrom === '' && $this->dateTo === '') {
            return 'Orders Pipeline — All Time';
        }

        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('d M Y') : null;
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->format('d M Y')   : null;

        if ($from && $to && $from === $to) {
            return "Orders Pipeline — {$from}";
        }

        $label = trim(($from ?? '') . ' → ' . ($to ?? ''), ' →');
        return "Orders Pipeline — {$label}";
    }

    #[On('huashu-date-range-updated')]
    public function updateDateRange(string $from, string $to): void
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
    }

    protected function getData(): array
    {
        // Build day-by-day range
        $startDate = $this->dateFrom !== ''
            ? Carbon::parse($this->dateFrom)->startOfDay()
            : Carbon::today()->subDays(29);

        $endDate = $this->dateTo !== ''
            ? Carbon::parse($this->dateTo)->endOfDay()
            : Carbon::today()->endOfDay();

        // Cap at 90 days for readability; if range > 90 days, switch to weekly buckets
        $diffDays = (int) $startDate->diffInDays($endDate);
        $useWeeks = $diffDays > 90;

        if ($useWeeks) {
            return $this->getWeeklyData($startDate, $endDate);
        }

        $days = collect();
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $days->push($cursor->copy());
            $cursor->addDay();
        }

        $statuses = [
            Order::STATUS_TRANSFERRED => ['label' => 'Transferred',         'color' => 'rgba(168, 85, 247, 0.7)', 'border' => 'rgb(168, 85, 247)'],
            Order::STATUS_FULFILLING  => ['label' => 'Fulfilling',          'color' => 'rgba(59, 130, 246, 0.7)', 'border' => 'rgb(59, 130, 246)'],
            Order::STATUS_DISPATCHED  => ['label' => 'Dispatched to Store', 'color' => 'rgba(251, 146, 60, 0.7)', 'border' => 'rgb(251, 146, 60)'],
            Order::STATUS_DELIVERED   => ['label' => 'Delivered',           'color' => 'rgba(34, 197, 94, 0.7)',  'border' => 'rgb(34, 197, 94)'],
        ];

        $rawCounts = Order::query()
            ->forHuashu()
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', $endDate->toDateString())
            ->selectRaw("DATE(created_at) as day, status, COUNT(*) as total")
            ->groupBy('day', 'status')
            ->get()
            ->groupBy('status');

        // Limit x-axis labels to avoid crowding
        $labelEvery = max(1, (int) ceil($days->count() / 30));
        $labels     = $days->map(fn ($d, $i) => $i % $labelEvery === 0 ? $d->format('d M') : '')->values()->toArray();
        $datasets   = [];

        foreach ($statuses as $statusKey => $meta) {
            $statusRows = $rawCounts->get($statusKey, collect())->keyBy('day');
            $data       = $days->map(fn ($d) => (int) ($statusRows[$d->toDateString()]->total ?? 0))->values()->toArray();

            $datasets[] = [
                'label'           => $meta['label'],
                'data'            => $data,
                'backgroundColor' => $meta['color'],
                'borderColor'     => $meta['border'],
                'borderWidth'     => 1,
            ];
        }

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    private function getWeeklyData(Carbon $startDate, Carbon $endDate): array
    {
        $weeks = collect();
        $cursor = $startDate->copy()->startOfWeek();
        while ($cursor->lte($endDate)) {
            $weeks->push($cursor->copy());
            $cursor->addWeek();
        }

        $statuses = [
            Order::STATUS_TRANSFERRED => ['label' => 'Transferred',         'color' => 'rgba(168, 85, 247, 0.7)', 'border' => 'rgb(168, 85, 247)'],
            Order::STATUS_FULFILLING  => ['label' => 'Fulfilling',          'color' => 'rgba(59, 130, 246, 0.7)', 'border' => 'rgb(59, 130, 246)'],
            Order::STATUS_DISPATCHED  => ['label' => 'Dispatched to Store', 'color' => 'rgba(251, 146, 60, 0.7)', 'border' => 'rgb(251, 146, 60)'],
            Order::STATUS_DELIVERED   => ['label' => 'Delivered',           'color' => 'rgba(34, 197, 94, 0.7)',  'border' => 'rgb(34, 197, 94)'],
        ];

        $rawCounts = Order::query()
            ->forHuashu()
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', $endDate->toDateString())
            ->selectRaw("DATE_TRUNC('week', created_at)::date as week, status, COUNT(*) as total")
            ->groupBy('week', 'status')
            ->get()
            ->groupBy('status');

        $labels   = $weeks->map(fn ($w) => $w->format('d M'))->values()->toArray();
        $datasets = [];

        foreach ($statuses as $statusKey => $meta) {
            $statusRows = $rawCounts->get($statusKey, collect())->keyBy('week');
            $data       = $weeks->map(fn ($w) => (int) ($statusRows[$w->toDateString()]->total ?? 0))->values()->toArray();
            $datasets[] = [
                'label'           => $meta['label'],
                'data'            => $data,
                'backgroundColor' => $meta['color'],
                'borderColor'     => $meta['border'],
                'borderWidth'     => 1,
            ];
        }

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => false, 'ticks' => ['maxTicksLimit' => 12, 'maxRotation' => 45]],
                'y' => ['stacked' => false, 'beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
