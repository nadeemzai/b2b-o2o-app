<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\TownshipStore;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 1;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Order Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('id')
                        ->label('Order #')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending'            => 'warning',
                            'preparing'          => 'info',
                            'ready_for_delivery' => 'primary',
                            'delivered'          => 'success',
                            'cancelled'          => 'danger',
                            default              => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending'            => 'Pending',
                            'preparing'          => 'Preparing',
                            'ready_for_delivery' => 'Ready for Delivery',
                            'delivered'          => 'Delivered',
                            'cancelled'          => 'Cancelled',
                            default              => ucfirst($state),
                        }),
                    TextEntry::make('payment_method')
                        ->label('Payment')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                    TextEntry::make('retailer.business_name')->label('Retailer'),
                    TextEntry::make('store.name')->label('Store'),
                    TextEntry::make('created_at')->label('Placed At')->dateTime(),
                    TextEntry::make('total_pkr')
                        ->label('Order Total')
                        ->money('PKR'),
                    TextEntry::make('collected_pkr')
                        ->label('Collected (PKR)')
                        ->money('PKR')
                        ->placeholder('—'),
                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Section::make('Order Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            TextEntry::make('product.name')->label('Product'),
                            TextEntry::make('qty')->label('Qty'),
                            TextEntry::make('unit_price_pkr')
                                ->label('Unit Price')
                                ->money('PKR'),
                            TextEntry::make('line_total_pkr')
                                ->label('Line Total')
                                ->money('PKR'),
                        ])
                        ->columns(4),
                ]),

            Section::make('Status History')
                ->schema([
                    RepeatableEntry::make('statusHistory')
                        ->label('')
                        ->schema([
                            TextEntry::make('from_status')
                                ->label('From')
                                ->formatStateUsing(fn (?string $state): string => $state
                                    ? str_replace('_', ' ', ucfirst($state))
                                    : '—')
                                ->placeholder('—'),
                            TextEntry::make('to_status')
                                ->label('To')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'pending'            => 'warning',
                                    'preparing'          => 'info',
                                    'ready_for_delivery' => 'primary',
                                    'delivered'          => 'success',
                                    'cancelled'          => 'danger',
                                    default              => 'gray',
                                })
                                ->formatStateUsing(fn (?string $state): string => $state
                                    ? str_replace('_', ' ', ucfirst($state))
                                    : '—'),
                            TextEntry::make('changedBy.name')
                                ->label('Changed By')
                                ->placeholder('—'),
                            TextEntry::make('note')
                                ->label('Note')
                                ->placeholder('—'),
                            TextEntry::make('created_at')
                                ->label('At')
                                ->dateTime(),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('retailer.business_name')
                    ->label('Retailer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('store.name')
                    ->label('Store')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'            => 'warning',
                        'preparing'          => 'info',
                        'ready_for_delivery' => 'primary',
                        'delivered'          => 'success',
                        'cancelled'          => 'danger',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'            => 'Pending',
                        'preparing'          => 'Preparing',
                        'ready_for_delivery' => 'Ready for Delivery',
                        'delivered'          => 'Delivered',
                        'cancelled'          => 'Cancelled',
                        default              => ucfirst($state),
                    }),
                TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'            => 'Pending',
                        'preparing'          => 'Preparing',
                        'ready_for_delivery' => 'Ready for Delivery',
                        'delivered'          => 'Delivered',
                        'cancelled'          => 'Cancelled',
                    ]),
                SelectFilter::make('store_id')
                    ->options(fn () => TownshipStore::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Store'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
