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
        return [];
    }
}
