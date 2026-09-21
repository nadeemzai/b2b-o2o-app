<?php

namespace App\Filament\Admin\Resources\TownshipStoreResource\Pages;

use App\Filament\Admin\Resources\TownshipStoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTownshipStore extends EditRecord
{
    protected static string $resource = TownshipStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
