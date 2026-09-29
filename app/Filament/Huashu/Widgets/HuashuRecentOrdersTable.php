<?php

namespace App\Filament\Huashu\Widgets;

use App\Filament\Huashu\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HuashuRecentOrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders — Huashu Pipeline';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->forHuashu()
                    ->with(['retailer', 'store'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->sortable(),

                Tables\Columns\TextColumn::make('retailer.business_name')
                    ->label('Retailer')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('OZ Store')
                    ->limit(20),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => Order::STATUS_TRANSFERRED,
                        'info'    => Order::STATUS_FULFILLING,
                        'warning' => Order::STATUS_DISPATCHED,
                        'success' => Order::STATUS_DELIVERED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_TRANSFERRED => 'Awaiting Fulfillment',
                        Order::STATUS_FULFILLING  => 'Fulfilling',
                        Order::STATUS_DISPATCHED  => 'Dispatched',
                        Order::STATUS_DELIVERED   => 'Delivered',
                        default                   => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->paginated(false);
    }
}
