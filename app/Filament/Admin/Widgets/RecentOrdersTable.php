<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

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
            ->filters([
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('until')
                            ->label('Until')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100]);
    }
}
