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
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Order::STATUS_PENDING))
                ->badge($badge(Order::STATUS_PENDING))
                ->badgeColor('warning'),

            'preparing' => Tab::make('Preparing')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Order::STATUS_PREPARING))
                ->badge($badge(Order::STATUS_PREPARING))
                ->badgeColor('info'),

            'ready_for_delivery' => Tab::make('Ready for Delivery')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Order::STATUS_READY_FOR_DELIVERY))
                ->badge($badge(Order::STATUS_READY_FOR_DELIVERY))
                ->badgeColor('primary'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Order::STATUS_DELIVERED))
                ->badge($badge(Order::STATUS_DELIVERED))
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Order::STATUS_CANCELLED))
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
