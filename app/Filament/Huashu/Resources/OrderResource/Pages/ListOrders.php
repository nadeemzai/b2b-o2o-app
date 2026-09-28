<?php

namespace App\Filament\Huashu\Resources\OrderResource\Pages;

use App\Filament\Huashu\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_xlsx')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(fn () => route('huashu.orders.export') . '?format=xlsx')
                ->openUrlInNewTab(),

            \Filament\Actions\Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => route('huashu.orders.export') . '?format=csv')
                ->openUrlInNewTab(),
        ];
    }
}
