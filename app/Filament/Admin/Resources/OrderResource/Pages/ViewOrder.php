<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Prepare (pending → preparing) ──────────────────────────
            Action::make('prepare')
                ->label('Start Preparing')
                ->icon('heroicon-o-fire')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Start Preparing Order')
                ->modalDescription('Mark this order as being prepared in-store.')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PENDING)
                ->action(function (): void {
                    $this->transitionOrder(
                        from: Order::STATUS_PENDING,
                        to:   Order::STATUS_PREPARING,
                        note: 'Order accepted and being prepared.',
                    );
                    Notification::make()->title('Order is now being prepared')->info()->send();
                    $this->refreshFormData(['status']);
                }),

            // ── Ready for Delivery (preparing → ready_for_delivery) ─────
            Action::make('ready_for_delivery')
                ->label('Mark Ready')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Mark as Ready for Delivery')
                ->modalDescription('Confirm that the order is packed and ready for rider pickup.')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PREPARING)
                ->action(function (): void {
                    $this->transitionOrder(
                        from: Order::STATUS_PREPARING,
                        to:   Order::STATUS_READY_FOR_DELIVERY,
                        note: 'Order packed and ready for delivery.',
                    );
                    Notification::make()->title('Order is ready for delivery')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            // ── Delivered (ready_for_delivery → delivered) ──────────────
            Action::make('deliver')
                ->label('Mark Delivered')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_READY_FOR_DELIVERY)
                ->form([
                    TextInput::make('collected_pkr')
                        ->label('Amount Collected (PKR)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('e.g. 4500.00')
                        ->helperText('Enter the cash amount collected from the retailer on delivery.')
                        ->required(fn (): bool => $this->record->payment_method === 'cod'),
                ])
                ->modalHeading('Confirm Delivery')
                ->modalDescription('Enter the amount collected and mark the order as delivered.')
                ->action(function (array $data): void {
                    $this->transitionOrder(
                        from:          Order::STATUS_READY_FOR_DELIVERY,
                        to:            Order::STATUS_DELIVERED,
                        note:          'Order delivered successfully.',
                        collectedPkr:  $data['collected_pkr'] ?? null,
                    );
                    Notification::make()->title('Order delivered')->success()->send();
                    $this->refreshFormData(['status', 'collected_pkr']);
                }),

            // ── Cancel (pending or preparing only) ──────────────────────
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
                    $this->transitionOrder(
                        from: $this->record->status,
                        to:   Order::STATUS_CANCELLED,
                        note: 'Cancelled: ' . $data['reason'],
                    );
                    Notification::make()->title('Order cancelled')->warning()->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }

    /**
     * Persist a status transition atomically:
     *  - update `orders.status` (and optionally `collected_pkr`)
     *  - insert a row into `order_status_history`
     */
    private function transitionOrder(
        string  $from,
        string  $to,
        string  $note = '',
        ?string $collectedPkr = null,
    ): void {
        DB::transaction(function () use ($from, $to, $note, $collectedPkr): void {
            $updates = ['status' => $to];
            if ($collectedPkr !== null) {
                $updates['collected_pkr'] = $collectedPkr;
            }

            $this->record->update($updates);

            OrderStatusHistory::create([
                'order_id'          => $this->record->id,
                'from_status'       => $from,
                'to_status'         => $to,
                'changed_by_user_id'=> auth()->id(),
                'note'              => $note,
            ]);
        });

        // Reload so header actions re-evaluate visibility
        $this->record->refresh();
    }
}
