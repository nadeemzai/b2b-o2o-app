<?php

namespace App\Filament\Store\Resources;

use App\Filament\Store\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\StoreStaff;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Orders';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $staff = StoreStaff::where('user_id', Auth::id())->where('is_active', true)->first();
        return parent::getEloquentQuery()
            ->when($staff, fn ($q) => $q->where('store_id', $staff->store_id))
            ->with(['retailer', 'store', 'items.product']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Order')->schema([
                Placeholder::make('order_number')->content(fn (Order $r) => $r->order_number),
                Placeholder::make('status')->content(fn (Order $r) => ucfirst(str_replace('_', ' ', $r->status))),
                Placeholder::make('retailer')->content(fn (Order $r) => $r->retailer->business_name ?? '—'),
                Placeholder::make('total_pkr')->label('Total (PKR)')->content(fn (Order $r) => 'PKR '.number_format($r->total_pkr, 2)),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('retailer.business_name')->label('Retailer')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'ready'      => 'primary',
                        'dispatched' => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('total_pkr')->label('Total (PKR)')->money('PKR')->sortable(),
                TextColumn::make('items_count')->label('Items')->counts('items'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'confirmed' => 'Confirmed', 'ready' => 'Ready',
                    'dispatched' => 'Dispatched', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
                Action::make('confirm')->icon('heroicon-o-check')->color('info')->requiresConfirmation()
                    ->visible(fn (Order $r): bool => $r->status === 'pending')
                    ->action(function (Order $r): void {
                        try { app(OrderStatusTransitionService::class)->transition($r, 'confirmed', Auth::user()); Notification::make()->title('Order confirmed')->success()->send(); }
                        catch (\Exception $e) { Notification::make()->title($e->getMessage())->danger()->send(); }
                    }),
                Action::make('mark_ready')->label('Mark Ready')->icon('heroicon-o-cube')->color('primary')->requiresConfirmation()
                    ->visible(fn (Order $r): bool => $r->status === 'confirmed')
                    ->action(function (Order $r): void {
                        try { app(OrderStatusTransitionService::class)->transition($r, 'ready', Auth::user()); Notification::make()->title('Order ready')->success()->send(); }
                        catch (\Exception $e) { Notification::make()->title($e->getMessage())->danger()->send(); }
                    }),
                Action::make('dispatch')->icon('heroicon-o-truck')->color('primary')->requiresConfirmation()
                    ->visible(fn (Order $r): bool => $r->status === 'ready')
                    ->action(function (Order $r): void {
                        try { app(OrderStatusTransitionService::class)->transition($r, 'dispatched', Auth::user()); Notification::make()->title('Order dispatched')->success()->send(); }
                        catch (\Exception $e) { Notification::make()->title($e->getMessage())->danger()->send(); }
                    }),
                Action::make('deliver')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (Order $r): bool => $r->status === 'dispatched')
                    ->form([
                        TextInput::make('collected_pkr')->label('Collected Amount (PKR)')->numeric()->required()->minValue(0),
                    ])
                    ->action(function (Order $r, array $data): void {
                        try { app(OrderService::class)->deliver($r, (float) $data['collected_pkr'], Auth::user()); Notification::make()->title('Delivered — COD collected')->success()->send(); }
                        catch (\Exception $e) { Notification::make()->title($e->getMessage())->danger()->send(); }
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
