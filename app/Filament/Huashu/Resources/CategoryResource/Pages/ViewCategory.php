<?php

namespace App\Filament\Huashu\Resources\CategoryResource\Pages;

use App\Filament\Huashu\Resources\CategoryResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * Read-only category view for Huashu (shows the OZ commission rates below).
 */
class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
