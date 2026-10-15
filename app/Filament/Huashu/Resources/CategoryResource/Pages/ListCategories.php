<?php

namespace App\Filament\Huashu\Resources\CategoryResource\Pages;

use App\Filament\Huashu\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    // No "New category" button — categories are created by OZ Admin only.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
