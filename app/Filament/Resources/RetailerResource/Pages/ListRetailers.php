<?php

namespace App\Filament\Resources\RetailerResource\Pages;

use App\Filament\Resources\RetailerResource;
use App\Models\Retailer;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRetailers extends ListRecords
{
    protected static string $resource = RetailerResource::class;

    protected function getHeaderActions(): array
    {
        return [];  // creation disabled
    }

    // Pending-first tabs with live badge count on the Pending tab
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('kyc_status', 'pending'))
                ->badge(Retailer::where('kyc_status', 'pending')->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('kyc_status', 'approved')),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('kyc_status', 'rejected')),

            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'pending';
    }
}
