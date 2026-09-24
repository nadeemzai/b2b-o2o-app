<?php

namespace App\Filament\Huashu\Resources;

use App\Filament\Huashu\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View as InfolistView;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationLabel = 'Fulfillment Orders';
    protected static ?int    $navigationSort  = 1;

    // ──────────────────────────────────────────────
    // Status helpers
    // ──────────────────────────────────────────────

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'transferred' => 'primary',
            'fulfilling'  => 'info',
            'delivered'   => 'success',
            default       => 'gray',
        };
    }

    private static function statusLabel(string $state): string
    {
        return match ($state) {
            'transferred' => 'Transferred — Awaiting Fulfillment',
            'fulfilling'  => 'Fulfilling (China → Township)',
            'delivered'   => 'Delivered',
            default       => ucwords(str_replace('_', ' ', $state)),
        };
    }

    // ──────────────────────────────────────────────
    // Scope — Huashu sees only transferred/fulfilling/delivered
    // ──────────────────────────────────────────────

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->forHuashu();
    }

    // ──────────────────────────────────────────────
    // Infolist (view page)
    // ──────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistView::make('filament.huashu.infolists.order-progress-bar')
                ->columnSpanFull()
                ->viewData(fn ($record) => ['record' => $record]),

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
                    TextEntry::make('transferred_to_huashu_at')
                        ->label('Transferred At')
                        ->dateTime()
                        ->placeholder('—'),
                    TextEntry::make('retailer.business_name')
                        ->label('Retailer / Buyer'),
                    TextEntry::make('store.name')
                        ->label('Township / Store'),
                    TextEntry::make('retailer.phone')
                        ->label('Retailer Phone')
                        ->placeholder('—'),
                    TextEntry::make('total_pkr')
                        ->label('Order Total (PKR)')
                        ->money('PKR'),
                    TextEntry::make('huashu_ref')
                        ->label('Huashu Reference')
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
                            TextEntry::make('product.name_en')
                                ->label('Product'),
                            TextEntry::make('variant_label')->label('Variant')->placeholder('—'),
                            TextEntry::make('qty')
                                ->label('Qty'),
                            TextEntry::make('huashu_unit_price_pkr')
                                ->label('Huashu Unit Price')
                                ->money('PKR')
                                ->placeholder('—'),
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
                                    ? self::statusLabel($state)
                                    : '—')
                                ->placeholder('—'),
                            TextEntry::make('to_status')
                                ->label('To')
                                ->badge()
                                ->color(fn (?string $state): string => $state
                                    ? self::statusColor($state)
                                    : 'gray')
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
                    ->label('Township / Store')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                    ->sortable(),
                TextColumn::make('total_pkr')
                    ->label('Total (PKR)')
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('huashu_ref')
                    ->label('Huashu Ref')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('transferred_to_huashu_at')
                    ->label('Transferred At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'transferred' => 'Transferred — Awaiting Fulfillment',
                        'fulfilling'  => 'Fulfilling (China → Township)',
                        'delivered'   => 'Delivered',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ── Mark as Fulfilling ──────────────────────────────────────
                Action::make('mark_fulfilling')
                    ->label('Mark as Fulfilling')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->canMarkFulfilling())
                    ->form([
                        TextInput::make('huashu_ref')
                            ->label('Huashu / China Seller Reference (optional)')
                            ->placeholder('e.g. HS-2024-001')
                            ->maxLength(100),
                    ])
                    ->modalHeading('Mark Order as Fulfilling')
                    ->modalDescription('This confirms that you have engaged the China seller and the order is now being processed.')
                    ->modalSubmitActionLabel('Mark as Fulfilling')
                    ->action(function (Order $record, array $data): void {
                        try {
                            app(OrderService::class)->markFulfilling(
                                $record,
                                auth()->id(),
                                $data['huashu_ref'] ?? null,
                            );
                            Notification::make()
                                ->title('Order marked as Fulfilling')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ── Mark as Delivered ───────────────────────────────────────
                Action::make('mark_delivered')
                    ->label('Mark as Delivered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->canMarkDelivered())
                    ->requiresConfirmation()
                    ->modalHeading('Mark Order as Delivered')
                    ->modalDescription('Confirm that the goods have arrived at the Huashu township/warehouse and the retailer has received them.')
                    ->modalSubmitActionLabel('Confirm Delivery')
                    ->action(function (Order $record): void {
                        try {
                            // Payment was already collected/verified by OZ — pass total_pkr as collected.
                            app(OrderService::class)->deliverOrder(
                                $record,
                                (float) $record->total_pkr,
                                auth()->id(),
                            );
                            Notification::make()
                                ->title('Order marked as Delivered')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('transferred_to_huashu_at', 'desc');
    }

    // ──────────────────────────────────────────────
    // Pages
    // ──────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
