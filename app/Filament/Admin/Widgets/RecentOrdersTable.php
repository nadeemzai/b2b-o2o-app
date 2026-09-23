<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['retailer', 'store'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->prefix('#'),

                Tables\Columns\TextColumn::make('retailer.business_name')
                    ->label('Retailer')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store')
                    ->limit(20),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => Order::STATUS_PENDING,
                        'primary' => Order::STATUS_PAYMENT_VERIFIED,
                        'purple'  => Order::STATUS_TRANSFERRED,
                        'yellow'  => Order::STATUS_FULFILLING,
                        'success' => Order::STATUS_DELIVERED,
                        'danger'  => Order::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING          => 'Pending',
                        Order::STATUS_PAYMENT_VERIFIED => 'Payment Verified',
                        Order::STATUS_TRANSFERRED      => 'Transferred',
                        Order::STATUS_FULFILLING       => 'Fulfilling',
                        Order::STATUS_DELIVERED        => 'Delivered',
                        Order::STATUS_CANCELLED        => 'Cancelled',
                        default                        => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed')
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
