<?php

namespace App\Filament\Admin\Resources\OrderAuditResource\Pages;

use App\Filament\Admin\Resources\OrderAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderAudits extends ListRecords
{
    protected static string $resource = OrderAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];   // read-only audit log — no create button
    }
}
