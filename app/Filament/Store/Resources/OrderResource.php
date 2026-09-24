<?php

namespace App\Filament\Store\Resources;

use App\Filament\Store\Resources\OrderResource\Pages;
use App\Filament\Store\StoreStaffContext;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $navigationGroup = 'Orders';

    // ──────────────────────────────────────────────
    // Status helpers (shared across table + infolist)
    // ──────────────────────────────────────────────

    private static function statusColor(string $state): string
    {
        return match ($state) {
            Order::STATUS_PENDING           => 'warning',
            Order::STATUS_PAYMENT_VERIFIED  => 'info',
            Order::STATUS_TRANSFERRED       => 'primary',
            Order::STATUS_FULFILLING        => 'info',
            Order::STATUS_DELIVERED         => 'success',
            Order::STATUS_CANCELLED         => 'danger',
            default                         => 'gray',
        };
    }

    private static function statusLabel(string $state): string
    {
        return match ($state) {
            Order::STATUS_PENDING          => 'Pending',
            Order::STATUS_PAYMENT_VERIFIED => 'Payment Verified',
            Order::STATUS_TRANSFERRED      => 'Transferred to Huashu',
            Order::STATUS_FULFILLING       => 'Fulfilling',
            Order::STATUS_DELIVERED        => 'Delivered',
            Order::STATUS_CANCELLED        => 'Cancelled',
            default                        => ucwords(str_replace('_', ' ', $state)),
        };
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $storeId = StoreStaffContext::storeId();

        return parent::getEloquentQuery()
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->with(['retailer', 'store', 'items.product', 'statusHistory.changedBy']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('Order #'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => self::statusColor($state))
                            ->formatStateUsing(fn (string $state): string => self::statusLabel($state)),
                        TextEntry::make('payment_method')->badge()->color('gray'),
                        TextEntry::make('retailer.business_name')->label('Retailer'),
                        TextEntry::make('store.name')->label('Store'),
                        TextEntry::make('created_at')->dateTime()->label('Placed At'),
                        TextEntry::make('total_pkr')->label('Total (PKR)')->money('PKR'),
                        TextEntry::make('collected_pkr')->label('Collected (PKR)')->money('PKR')->placeholder('—'),
                        TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product.name_en')->label('Product'),
                                TextEntry::make('qty')->label('Qty'),
                                TextEntry::make('unit_price_pkr')->label('Unit Price')->money('PKR'),
                                TextEntry::make('line_total_pkr')->label('Line Total')->money('PKR'),
                            ]),
                    ]),

                Section::make('Status History')
                    ->schema([
                        RepeatableEntry::make('statusHistory')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('from_status')
                                    ->formatStateUsing(fn (?string $state): string => $state ? self::statusLabel($state) : '—'),
                                TextEntry::make('to_status')
                                    ->badge()
                                    ->color(fn (string $state): string => self::statusColor($state))
                                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state)),
                                TextEntry::make('changedBy.name')->label('By')->placeholder('—'),
                                TextEntry::make('note')->placeholder('—'),
                                TextEntry::make('created_at')->dateTime()->label('At'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Order #')->sortable(),
                TextColumn::make('retailer.business_name')->label('Retailer')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('total_pkr')->label('Total (PKR)')->money('PKR')->sortable(),
                TextColumn::make('items_count')->label('Items')->counts('items'),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Placed'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING           => 'Pending',
                        Order::STATUS_PAYMENT_VERIFIED   => 'Payment Verified',
                        Order::STATUS_TRANSFERRED        => 'Transferred to Huashu',
                        Order::STATUS_FULFILLING         => 'Fulfilling',
                        Order::STATUS_DELIVERED          => 'Delivered',
                        Order::STATUS_CANCELLED          => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
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
