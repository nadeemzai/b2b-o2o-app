<?php

namespace App\Filament\Huashu\Resources\OrderResource\Pages;

use App\Filament\Huashu\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Mark as Fulfilling (transferred → fulfilling) ───────────
            Action::make('mark_fulfilling')
                ->label('Mark as Fulfilling')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => $this->record->canMarkFulfilling())
                ->form([
                    TextInput::make('huashu_ref')
                        ->label('Huashu / China Seller Reference (optional)')
                        ->placeholder('e.g. HS-2024-001')
                        ->maxLength(100),
                ])
                ->modalHeading('Mark Order as Fulfilling')
                ->modalDescription('This confirms that you have engaged the China seller and the order is now being processed.')
                ->modalSubmitActionLabel('Mark as Fulfilling')
                ->action(function (array $data): void {
                    try {
                        app(OrderService::class)->markFulfilling(
                            $this->record,
                            auth()->id(),
                            $data['huashu_ref'] ?? null,
                        );
                        Notification::make()->title('Order marked as Fulfilling')->success()->send();
                        $this->record->refresh();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),

            // ── Mark as Delivered (fulfilling → delivered) ──────────────
            Action::make('mark_delivered')
                ->label('Mark as Delivered')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->canMarkDelivered())
                ->requiresConfirmation()
                ->modalHeading('Mark Order as Delivered')
                ->modalDescription('Confirm that the goods have arrived at the Huashu township/warehouse and the retailer has received them.')
                ->modalSubmitActionLabel('Confirm Delivery')
                ->action(function (): void {
                    try {
                        app(OrderService::class)->deliverOrder(
                            $this->record,
                            (float) $this->record->total_pkr,
                            auth()->id(),
                        );
                        Notification::make()->title('Order marked as Delivered')->success()->send();
                        $this->record->refresh();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),

            // ── Back ─────────────────────────────────────────────────────
            Action::make('back')
                ->label('← Back to Orders')
                ->url(OrderResource::getUrl())
                ->color('gray'),
        ];
    }
}
