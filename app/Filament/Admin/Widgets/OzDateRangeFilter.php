<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;

class OzDateRangeFilter extends Widget
{
    protected static ?int $sort = 0;

    protected static string $view = 'filament.admin.widgets.oz-date-range-filter';

    protected int | string | array $columnSpan = 'full';

    public string $dateFrom    = '';
    public string $dateTo      = '';
    public string $activePreset = 'month';

    public function mount(): void
    {
        $this->applyPreset('month');
    }

    public function applyPreset(string $preset): void
    {
        $this->activePreset = $preset;

        match ($preset) {
            'today'  => [$this->dateFrom, $this->dateTo] = [now()->toDateString(), now()->toDateString()],
            'week'   => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month'  => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'days30' => [$this->dateFrom, $this->dateTo] = [now()->subDays(29)->toDateString(), now()->toDateString()],
            'days90' => [$this->dateFrom, $this->dateTo] = [now()->subDays(89)->toDateString(), now()->toDateString()],
            'all'    => [$this->dateFrom, $this->dateTo] = ['', ''],
            default  => null,
        };

        $this->pushDateRange();
    }

    public function applyCustom(): void
    {
        $this->activePreset = 'custom';
        $this->pushDateRange();
    }

    public function getRangeLabel(): string
    {
        if ($this->dateFrom === '' && $this->dateTo === '') {
            return 'All Time';
        }

        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->format('d M Y') : '—';
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->format('d M Y')   : '—';

        return $from === $to ? $from : "{$from} → {$to}";
    }

    private function pushDateRange(): void
    {
        $this->dispatch('oz-date-range-updated', from: $this->dateFrom, to: $this->dateTo);
    }
}
