<?php

namespace App\Filament\Admin\Pages;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon    = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel   = 'Commission Report';
    protected static ?string $navigationGroup   = 'Operations';
    protected static ?int    $navigationSort     = 2;
    protected static string  $view              = 'filament.admin.pages.commission-dashboard';
    protected static ?string $title             = 'Commission Report';

    // ── Filter state ──────────────────────────────────────────
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    public int $perPage = 20;
    public int $page    = 1;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
        $this->form->fill([
            'dateFrom' => $this->dateFrom,
            'dateTo'   => $this->dateTo,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('dateFrom')
                ->label('From')
                ->native(false)
                ->maxDate(now())
                ->reactive()
                ->afterStateUpdated(fn () => $this->page = 1),
            DatePicker::make('dateTo')
                ->label('To')
                ->native(false)
                ->maxDate(now())
                ->reactive()
                ->afterStateUpdated(fn () => $this->page = 1),
        ])->columns(2);
    }

    // ── Computed stats ────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->endOfDay()     : null;

        $base = Order::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('created_at', '<=', $to));

        $transferred = (clone $base)->whereIn('status', [
            Order::STATUS_TRANSFERRED,
            Order::STATUS_FULFILLING,
            Order::STATUS_DELIVERED,
        ]);

        return [
            'total_commission'    => (clone $transferred)->sum('oz_commission_pkr'),
            'delivered_commission'=> (clone $base)->where('status', Order::STATUS_DELIVERED)->sum('oz_commission_pkr'),
            'pending_verification'=> (clone $base)->where('status', Order::STATUS_PENDING)->count(),
            'transferred_count'   => (clone $transferred)->count(),
            'delivered_count'     => (clone $base)->where('status', Order::STATUS_DELIVERED)->count(),
            'total_order_value'   => (clone $transferred)->sum('total_pkr'),
        ];
    }

    // ── Paginated orders table ────────────────────────────────

    public function getOrdersProperty()
    {
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->endOfDay()     : null;

        return Order::with(['retailer', 'store', 'paymentVerifiedBy'])
            ->whereIn('status', [
                Order::STATUS_TRANSFERRED,
                Order::STATUS_FULFILLING,
                Order::STATUS_DELIVERED,
            ])
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('created_at', '<=', $to))
            ->orderByDesc('transferred_to_huashu_at')
            ->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function applyFilter(): void
    {
        $this->page = 1;
    }

    public function resetFilter(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
        $this->form->fill([
            'dateFrom' => $this->dateFrom,
            'dateTo'   => $this->dateTo,
        ]);
        $this->page = 1;
    }

    public function formatMoney(float|string|null $value): string
    {
        if ($value === null) {
            return '—';
        }
        return 'PKR ' . number_format((float) $value, 2);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'transferred' => 'Transferred',
            'fulfilling'  => 'Fulfilling',
            'delivered'   => 'Delivered',
            default       => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'transferred' => 'primary',
            'fulfilling'  => 'info',
            'delivered'   => 'success',
            default       => 'gray',
        };
    }
}
