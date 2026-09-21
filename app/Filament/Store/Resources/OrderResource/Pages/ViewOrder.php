<?php

namespace App\Filament\Store\Resources\OrderResource\Pages;

use App\Filament\Store\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Prepare ─────────────────────────────────────────────────────
            Action::make('prepare')
                ->label('Start Preparing')
                ->icon('heroicon-o-fire')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Start preparing this order?')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PENDING)
                ->action(function (): void {
                    try {
                        app(OrderStatusTransitionService::class)
                            ->transition($this->record, Order::STATUS_PREPARING, Auth::id(), 'Order accepted and being prepared.');
                        $this->record->refresh();
                        Notification::make()->title('Order is now being prepared')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            // ── Ready for Delivery ───────────────────────────────────────────
            Action::make('ready_for_delivery')
                ->label('Mark Ready')
                ->icon('heroicon-o-cube')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Mark order as ready for delivery?')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PREPARING)
                ->action(function (): void {
                    try {
                        app(OrderStatusTransitionService::class)
                            ->transition($this->record, Order::STATUS_READY_FOR_DELIVERY, Auth::id(), 'Order packed and ready for delivery.');
                        $this->record->refresh();
                        Notification::make()->title('Order is ready for delivery')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            // ── Deliver (with COD collection) ────────────────────────────────
            Action::make('deliver')
                ->label('Mark Delivered')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_READY_FOR_DELIVERY)
                ->form(function (): array {
                    $fields = [];
                    if ($this->record->payment_method === 'cod') {
                        $fields[] = TextInput::make('collected_pkr')
                            ->label('Cash Collected (PKR)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->helperText('Enter the exact amount collected from the retailer.');
                    }
                    return $fields;
                })
                ->action(function (array $data): void {
                    try {
                        $collected = (float) ($data['collected_pkr'] ?? $this->record->total_pkr);
                        app(OrderService::class)->deliverOrder($this->record, $collected, Auth::id());
                        $this->record->refresh();
                        Notification::make()->title('Order delivered — PKR ' . number_format($collected, 2) . ' collected')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            // ── Cancel ───────────────────────────────────────────────────────
            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->isCancellable())
                ->form([
                    Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    try {
                        app(OrderService::class)->cancelOrder($this->record, Auth::id());
                        $this->record->refresh();
                        Notification::make()->title('Order cancelled')->warning()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
