<?php

namespace App\Filament\Admin\Resources\RetailerResource\Pages;

use App\Filament\Admin\Resources\RetailerResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRetailers extends ListRecords
{
    protected static string $resource = RetailerResource::class;

    public function getTabs(): array
    {
        return [
            'pending'  => Tab::make('Pending KYC')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kyc_status', 'pending'))
                ->badge(fn () => \App\Models\Retailer::where('kyc_status', 'pending')->count()),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kyc_status', 'approved')),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kyc_status', 'rejected')),
            'all'      => Tab::make('All'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
