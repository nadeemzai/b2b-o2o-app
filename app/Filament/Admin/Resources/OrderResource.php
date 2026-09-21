<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Order Info')->schema([
                Placeholder::make('order_number')->content(fn (Order $r) => $r->order_number),
                Placeholder::make('status')->content(fn (Order $r) => ucfirst(str_replace('_', ' ', $r->status))),
                Placeholder::make('retailer')->content(fn (Order $r) => $r->retailer->business_name ?? '—'),
                Placeholder::make('store')->content(fn (Order $r) => $r->store->name ?? '—'),
                Placeholder::make('total_pkr')
                    ->label('Order Total (PKR)')
                    ->content(fn (Order $r) => 'PKR '.number_format($r->total_pkr, 2)),
                Placeholder::make('payment_method')->content(fn (Order $r) => strtoupper($r->payment_method)),
                Placeholder::make('collected_pkr')
                    ->label('Collected (PKR)')
                    ->content(fn (Order $r) => $r->collected_pkr ? 'PKR '.number_format($r->collected_pkr, 2) : '—'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('retailer.business_name')->label('Retailer')->searchable()->sortable(),
                TextColumn::make('store.name')->label('Store')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'ready'      => 'primary',
                        'dispatched' => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('payment_method')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'confirmed'  => 'Confirmed',
                        'ready'      => 'Ready',
                        'dispatched' => 'Dispatched',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                    ]),
                SelectFilter::make('store_id')
                    ->options(fn () => \App\Models\TownshipStore::orderBy('name')->pluck('name', 'id')->toArray())
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
