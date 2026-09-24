<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $badge = fn (string $status) => ($counts[$status] ?? 0) ?: null;

        return [
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_PENDING))
                ->badge($badge(Order::STATUS_PENDING))
                ->badgeColor('warning'),

            'payment_verified' => Tab::make('Payment Verified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_PAYMENT_VERIFIED))
                ->badge($badge(Order::STATUS_PAYMENT_VERIFIED))
                ->badgeColor('info'),

            'transferred' => Tab::make('Transferred to Huashu')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_TRANSFERRED))
                ->badge($badge(Order::STATUS_TRANSFERRED))
                ->badgeColor('primary'),

            'fulfilling' => Tab::make('Fulfilling')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_FULFILLING))
                ->badge($badge(Order::STATUS_FULFILLING))
                ->badgeColor('info'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_DELIVERED))
                ->badge($badge(Order::STATUS_DELIVERED))
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_CANCELLED))
                ->badge($badge(Order::STATUS_CANCELLED))
                ->badgeColor('danger'),

            'all' => Tab::make('All'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_xlsx')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(fn () => route('admin.orders.export') . '?' . http_build_query(array_filter([
                    'format' => 'xlsx',
                    'status' => ($tab = $this->activeTab) && $tab !== 'all' ? $tab : null,
                ])))
                ->openUrlInNewTab(),

            \Filament\Actions\Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => route('admin.orders.export') . '?' . http_build_query(array_filter([
                    'format' => 'csv',
                    'status' => ($tab = $this->activeTab) && $tab !== 'all' ? $tab : null,
                ])))
                ->openUrlInNewTab(),
        ];
    }
}
