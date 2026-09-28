<?php

namespace App\Filament\Admin\Resources\TownshipStoreResource\Pages;

use App\Filament\Admin\Resources\TownshipStoreResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTownshipStore extends ViewRecord
{
    protected static string $resource = TownshipStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
