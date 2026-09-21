<?php

namespace App\Filament\Store\Resources\OrderResource\Pages;

use App\Filament\Store\Resources\OrderResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'pending'    => Tab::make('Pending')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'confirmed'  => Tab::make('Confirmed')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')),
            'ready'      => Tab::make('Ready')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'ready')),
            'dispatched' => Tab::make('Dispatched')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dispatched')),
            'all'        => Tab::make('All'),
        ];
    }
}
