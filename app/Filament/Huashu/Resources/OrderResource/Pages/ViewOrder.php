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

            // ── Mark as Dispatched (fulfilling → dispatched) ────────────
            Action::make('mark_dispatched')
                ->label('Mark as Dispatched')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn (): bool => $this->record->canMarkDispatched())
                ->requiresConfirmation()
                ->modalHeading('Mark Order as Dispatched')
                ->modalDescription('Confirm that the goods have been shipped from Huashu to the OZ township store. OZ Admin will complete the final delivery confirmation.')
                ->modalSubmitActionLabel('Confirm Dispatch')
                ->action(function (): void {
                    try {
                        app(OrderService::class)->markDispatched(
                            $this->record,
                            auth()->id(),
                        );
                        Notification::make()->title('Order marked as Dispatched')->success()->send();
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
