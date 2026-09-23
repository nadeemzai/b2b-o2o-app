<?php

namespace App\Filament\Huashu\Resources\OrderResource\Pages;

use App\Filament\Huashu\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('← Back to Orders')
                ->url(OrderResource::getUrl())
                ->color('gray'),
        ];
    }
}
