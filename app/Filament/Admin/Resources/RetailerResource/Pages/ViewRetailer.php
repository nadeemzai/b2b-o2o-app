<?php

namespace App\Filament\Admin\Resources\RetailerResource\Pages;

use App\Filament\Admin\Resources\RetailerResource;
use App\Models\Retailer;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRetailer extends ViewRecord
{
    protected static string $resource = RetailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Approve — visible when pending
            Action::make('approve')
                ->label('Approve KYC')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve KYC')
                ->modalDescription('Approve this retailer. They will be able to browse products and place orders.')
                ->visible(fn (): bool => $this->record->kyc_status === 'pending')
                ->action(function (): void {
                    $this->record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                    Notification::make()->title('Retailer approved successfully')->success()->send();
                    $this->refreshFormData(['kyc_status', 'kyc_rejection_reason']);
                }),

            // Reject — visible when pending
            Action::make('reject')
                ->label('Reject KYC')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->kyc_status === 'pending')
                ->form([
                    Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(1000)
                        ->placeholder('Explain why the documents were rejected...'),
                ])
                ->modalHeading('Reject KYC Application')
                ->action(function (array $data): void {
                    $this->record->update([
                        'kyc_status'           => 'rejected',
                        'kyc_rejection_reason' => $data['reason'],
                    ]);
                    Notification::make()->title('Retailer KYC rejected')->warning()->send();
                    $this->refreshFormData(['kyc_status', 'kyc_rejection_reason']);
                }),

            // Re-approve — visible when rejected
            Action::make('re_approve')
                ->label('Re-approve')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Re-approve KYC')
                ->modalDescription('Clear the rejection and approve this retailer.')
                ->visible(fn (): bool => $this->record->kyc_status === 'rejected')
                ->action(function (): void {
                    $this->record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                    Notification::make()->title('Retailer re-approved')->success()->send();
                    $this->refreshFormData(['kyc_status', 'kyc_rejection_reason']);
                }),
        ];
    }
}
