<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Verify Payment (pending → payment_verified) ─────────────
            Action::make('verify_payment')
                ->label('Verify Payment')
                ->icon('heroicon-o-check-badge')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Confirm Payment Received')
                ->modalDescription('Has the retailer\'s payment been confirmed? This cannot be undone easily.')
                ->modalSubmitActionLabel('Yes, Verify Payment')
                ->visible(fn (): bool => $this->record->canVerifyPayment())
                ->action(function (): void {
                    app(OrderService::class)->verifyPayment($this->record, auth()->id());
                    Notification::make()->title('Payment verified')->success()->send();
                    $this->record->refresh();
                    $this->refreshFormData(['status']);
                }),

            // ── Transfer to Huashu (payment_verified → transferred) ─────
            Action::make('transfer_to_huashu')
                ->label('Transfer to Huashu')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Transfer Order to Huashu')
                ->modalDescription('This will hand the order over to Huashu for fulfillment and lock in the OZ commission. Proceed?')
                ->modalSubmitActionLabel('Transfer Now')
                ->visible(fn (): bool => $this->record->canTransferToHuashu())
                ->action(function (): void {
                    app(OrderService::class)->transferToHuashu($this->record, auth()->id());
                    Notification::make()->title('Order transferred to Huashu')->success()->send();
                    $this->record->refresh();
                    $this->refreshFormData(['status']);
                }),

            // ── Cancel (pending or payment_verified only) ───────────────
            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->isCancellable())
                ->form([
                    Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('Explain why the order is being cancelled...'),
                ])
                ->modalHeading('Cancel Order')
                ->requiresConfirmation(false)
                ->action(function (array $data): void {
                    app(OrderService::class)->cancelOrder($this->record, auth()->id(), 'Cancelled: ' . $data['reason']);
                    Notification::make()->title('Order cancelled')->warning()->send();
                    $this->record->refresh();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
