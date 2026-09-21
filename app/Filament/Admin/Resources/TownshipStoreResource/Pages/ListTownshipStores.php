<?php

namespace App\Filament\Admin\Resources\TownshipStoreResource\Pages;

use App\Filament\Admin\Resources\TownshipStoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTownshipStores extends ListRecords
{
    protected static string $resource = TownshipStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
