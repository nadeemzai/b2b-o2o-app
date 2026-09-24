<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\TownshipStore;
use App\Services\OrderService;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View as InfolistView;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
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

    // ──────────────────────────────────────────────
    // Status helpers (shared across table + infolist)
    // ──────────────────────────────────────────────

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'pending'          => 'warning',
            'payment_verified' => 'info',
            'transferred'      => 'primary',
            'fulfilling'       => 'info',
            'delivered'        => 'success',
            'cancelled'        => 'danger',
            default            => 'gray',
        };
    }

    private static function statusLabel(string $state): string
    {
        return match ($state) {
            'pending'          => 'Pending',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Transferred to Huashu',
            'fulfilling'       => 'Fulfilling',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
            default            => ucwords(str_replace('_', ' ', $state)),
        };
    }

    // ──────────────────────────────────────────────
    // Infolist
    // ──────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistView::make('filament.admin.infolists.order-progress-bar')
                ->columnSpanFull(),

            Section::make('Order Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('id')
                        ->label('Order #')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => self::statusColor($state))
                        ->formatStateUsing(fn (string $state): string => self::statusLabel($state)),
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

            Section::make('OZ Commission & Transfer')
                ->columns(3)
                ->schema([
                    TextEntry::make('oz_commission_pkr')
                        ->label('OZ Commission (PKR)')
                        ->money('PKR')
                        ->placeholder('Calculated on transfer'),
                    TextEntry::make('payment_verified_at')
                        ->label('Payment Verified At')
                        ->dateTime()
                        ->placeholder('—'),
                    TextEntry::make('paymentVerifiedBy.name')
                        ->label('Verified By')
                        ->placeholder('—'),
                    TextEntry::make('transferred_to_huashu_at')
                        ->label('Transferred At')
                        ->dateTime()
                        ->placeholder('—'),
                    TextEntry::make('transferredBy.name')
                        ->label('Transferred By')
                        ->placeholder('—'),
                    TextEntry::make('huashu_ref')
                        ->label('Huashu Ref')
                        ->placeholder('—'),
                ])
                ->hidden(fn (Order $record): bool =>
                    ! in_array($record->status, [
                        'payment_verified', 'transferred', 'fulfilling', 'delivered',
                    ])
                ),

            Section::make('Payment Proof')
                ->columns(1)
                ->schema([
                    ImageEntry::make('payment_proof_path')
                        ->label('Uploaded Proof Image')
                        ->disk('public')
                        ->height(320)
                        ->width('auto')
                        ->extraImgAttributes(['class' => 'rounded-lg border border-gray-200 shadow-sm'])
                        ->placeholder('No proof uploaded yet')
                        ->visible(fn (Order $record): bool => (bool) $record->payment_proof_path),
                    TextEntry::make('payment_proof_path')
                        ->label('Proof Status')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? 'Proof Submitted'
                            : 'No proof uploaded yet')
                        ->color(fn (?string $state): string => $state ? 'success' : 'warning')
                        ->hidden(fn (Order $record): bool => (bool) $record->payment_proof_path),
                ])
                ->hidden(fn (Order $record): bool => $record->status === 'cancelled'),

            Section::make('Order Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            TextEntry::make('product.name')->label('Product'),
                            TextEntry::make('variant_label')->label('Variant')->placeholder('—'),
                            TextEntry::make('qty')->label('Qty'),
                            TextEntry::make('unit_price_pkr')
                                ->label('Retailer Price')
                                ->money('PKR'),
                            TextEntry::make('huashu_unit_price_pkr')
                                ->label('Huashu Price')
                                ->money('PKR')
                                ->placeholder('—'),
                            TextEntry::make('line_total_pkr')
                                ->label('Line Total')
                                ->money('PKR'),
                        ])
                        ->columns(5),
                ]),

            Section::make('Status History')
                ->schema([
                    RepeatableEntry::make('statusHistory')
                        ->label('')
                        ->schema([
                            TextEntry::make('from_status')
                                ->label('From')
                                ->formatStateUsing(fn (?string $state): string => $state
                                    ? self::statusLabel($state)
                                    : '—')
                                ->placeholder('—'),
                            TextEntry::make('to_status')
                                ->label('To')
                                ->badge()
                                ->color(fn (?string $state): string => $state ? self::statusColor($state) : 'gray')
                                ->formatStateUsing(fn (?string $state): string => $state
                                    ? self::statusLabel($state)
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

    // ──────────────────────────────────────────────
    // Table
    // ──────────────────────────────────────────────

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
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state)),
                TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('oz_commission_pkr')
                    ->label('Commission (PKR)')
                    ->money('PKR')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                IconColumn::make('payment_proof_path')
                    ->label('Proof')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus-small')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (?string $state): string => $state ? 'Payment proof uploaded' : 'No proof yet'),
                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'          => 'Pending',
                        'payment_verified' => 'Payment Verified',
                        'transferred'      => 'Transferred to Huashu',
                        'fulfilling'       => 'Fulfilling',
                        'delivered'        => 'Delivered',
                        'cancelled'        => 'Cancelled',
                    ]),
                SelectFilter::make('store_id')
                    ->options(fn () => TownshipStore::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Store'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ── Verify Payment ─────────────────────────────────────────
                Action::make('verify_payment')
                    ->label('Verify Payment')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Payment Received')
                    ->modalDescription('Has the retailer\'s payment been confirmed? This cannot be undone easily.')
                    ->modalSubmitActionLabel('Yes, Verify Payment')
                    ->visible(fn (Order $record): bool => $record->canVerifyPayment())
                    ->action(function (Order $record): void {
                        try {
                            app(OrderService::class)->verifyPayment($record, auth()->id());
                            Notification::make()
                                ->title('Payment verified')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ── Transfer to Huashu ─────────────────────────────────────
                Action::make('transfer_to_huashu')
                    ->label('Transfer to Huashu')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Transfer Order to Huashu')
                    ->modalDescription('This will hand the order over to Huashu for fulfillment and lock in the OZ commission. Proceed?')
                    ->modalSubmitActionLabel('Transfer Now')
                    ->visible(fn (Order $record): bool => $record->canTransferToHuashu())
                    ->action(function (Order $record): void {
                        try {
                            app(OrderService::class)->transferToHuashu($record, auth()->id());
                            Notification::make()
                                ->title('Order transferred to Huashu')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ── Cancel ─────────────────────────────────────────────────
                Action::make('cancel_order')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription('Are you sure you want to cancel this order? Stock reservations will be released.')
                    ->visible(fn (Order $record): bool => $record->isCancellable())
                    ->action(function (Order $record): void {
                        try {
                            app(OrderService::class)->cancelOrder($record, auth()->id(), 'Cancelled by admin.');
                            Notification::make()
                                ->title('Order cancelled')
                                ->warning()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
