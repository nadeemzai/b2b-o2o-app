<?php

namespace App\Filament\Store\Resources\OrderResource\Pages;

use App\Filament\Store\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;

// Read-only — store staff no longer transition order status or mark delivery.
// The Huashu wholesale flow (OZ admin verifies payment + transfers, Huashu
// fulfills and delivers) owns that lifecycle now.
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
}
