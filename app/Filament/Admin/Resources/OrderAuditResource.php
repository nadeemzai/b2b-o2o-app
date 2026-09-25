<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderAuditResource\Pages;
use App\Models\OrderStatusHistory;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderAuditResource extends Resource
{
    protected static ?string $model = OrderStatusHistory::class;

    protected static ?string $navigationIcon    = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup   = 'Operations';
    protected static ?string $navigationLabel   = 'Order Audit Trail';
    protected static ?string $breadcrumb        = 'Order Audit Trail';
    protected static ?int    $navigationSort    = 2;

    // ────────────────────────────────────────────────────────────────
    // Helpers shared between table columns and filters
    // ────────────────────────────────────────────────────────────────

    private static function statusColor(?string $state): string
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

    private static function statusLabel(?string $state): string
    {
        if ($state === null) {
            return '—';
        }
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

    private static function statusOptions(): array
    {
        return [
            'pending'          => 'Pending',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Transferred to Huashu',
            'fulfilling'       => 'Fulfilling',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
        ];
    }

    // ────────────────────────────────────────────────────────────────
    // Table
    // ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                OrderStatusHistory::query()
                    ->with(['order.retailer', 'order.store', 'changedBy'])
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('order.id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('order', fn ($q) => $q->where('id', $search));
                    })
                    ->url(fn (OrderStatusHistory $record): string =>
                        OrderResource::getUrl('view', ['record' => $record->order_id])
                    )
                    ->color('primary')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                TextColumn::make('order.retailer.business_name')
                    ->label('Retailer')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('order.retailer', fn ($q) =>
                            $q->where('business_name', 'like', "%{$search}%")
                        );
                    })
                    ->limit(28),

                TextColumn::make('order.store.name')
                    ->label('Store')
                    ->limit(24)
                    ->placeholder('—'),

                TextColumn::make('from_status')
                    ->label('From')
                    ->badge()
                    ->color(fn (?string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => self::statusLabel($state))
                    ->placeholder('—'),

                TextColumn::make('to_status')
                    ->label('To')
                    ->badge()
                    ->color(fn (?string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => self::statusLabel($state)),

                TextColumn::make('changedBy.name')
                    ->label('Changed By')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('changedBy', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        );
                    })
                    ->limit(24)
                    ->placeholder('—'),

                TextColumn::make('note')
                    ->label('Note')
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(fn (?string $state): ?string => strlen((string) $state) > 50 ? $state : null),

                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('to_status')
                    ->label('Transitioned To')
                    ->options(self::statusOptions()),

                SelectFilter::make('from_status')
                    ->label('Transitioned From')
                    ->options(self::statusOptions()),

                Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_order')
                    ->label('View Order')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (OrderStatusHistory $record): string =>
                        OrderResource::getUrl('view', ['record' => $record->order_id])
                    ),
            ])
            ->bulkActions([])
            ->striped();
    }

    // ────────────────────────────────────────────────────────────────
    // Pages — list only (read-only audit log, no create/edit)
    // ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderAudits::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
