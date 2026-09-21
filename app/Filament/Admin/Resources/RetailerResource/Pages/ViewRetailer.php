<?php

namespace App\Filament\Admin\Resources\RetailerResource\Pages;

use App\Filament\Admin\Resources\RetailerResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;

class ViewRetailer extends ViewRecord
{
    protected static string $resource = RetailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->kyc_status === 'pending')
                ->action(function (): void {
                    $this->record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                    Notification::make()->title('Retailer approved')->success()->send();
                    $this->refreshFormData(['kyc_status', 'kyc_rejection_reason']);
                }),
            Action::make('reject')
                ->color('danger')
                ->visible(fn () => $this->record->kyc_status === 'pending')
                ->form([
                    Textarea::make('reason')->required()->maxLength(1000),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'kyc_status'           => 'rejected',
                        'kyc_rejection_reason' => $data['reason'],
                    ]);
                    Notification::make()->title('Retailer rejected')->warning()->send();
                    $this->refreshFormData(['kyc_status', 'kyc_rejection_reason']);
                }),
        ];
    }
}
