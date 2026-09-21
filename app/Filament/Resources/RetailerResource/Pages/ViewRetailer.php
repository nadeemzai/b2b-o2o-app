<?php

namespace App\Filament\Resources\RetailerResource\Pages;

use App\Filament\Resources\RetailerResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRetailer extends ViewRecord
{
    protected static string $resource = RetailerResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // ── Approve ───────────────────────────────────────────────────
            Actions\Action::make('approve')
                ->label('Approve KYC')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve KYC Application')
                ->modalDescription(
                    'Approving grants this retailer full catalogue and ordering access. '
                    . 'You can reverse this decision with the Reset button.'
                )
                ->modalSubmitActionLabel('Yes, Approve')
                ->visible(fn (): bool => $this->record->isPending())
                ->action(function (): void {
                    $this->record->update([
                        'kyc_status'           => 'approved',
                        'kyc_rejection_reason' => null,
                    ]);

                    Notification::make()
                        ->title('KYC Approved')
                        ->body("{$this->record->business_name} is now active and can place orders.")
                        ->success()
                        ->send();

                    $this->redirect(RetailerResource::getUrl('view', ['record' => $this->record]));
                }),

            // ── Reject ────────────────────────────────────────────────────
            Actions\Action::make('reject')
                ->label('Reject KYC')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject KYC Application')
                ->modalDescription(
                    'The retailer will see this reason on their dashboard. '
                    . 'Be specific so they know exactly what to fix and re-submit.'
                )
                ->modalSubmitActionLabel('Reject Application')
                ->form([
                    Textarea::make('kyc_rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->minLength(10)
                        ->maxLength(500)
                        ->rows(4)
                        ->placeholder(
                            'e.g. "CNIC images are too blurry to read. '
                            . 'Please re-upload clear, well-lit photographs of both sides."'
                        ),
                ])
                ->visible(fn (): bool => $this->record->isPending() || $this->record->isApproved())
                ->action(function (array $data): void {
                    $this->record->update([
                        'kyc_status'           => 'rejected',
                        'kyc_rejection_reason' => $data['kyc_rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('KYC Rejected')
                        ->body("{$this->record->business_name} has been notified of the rejection reason.")
                        ->warning()
                        ->send();

                    $this->redirect(RetailerResource::getUrl('view', ['record' => $this->record]));
                }),

            // ── Reset to Pending ──────────────────────────────────────────
            Actions\Action::make('reset_pending')
                ->label('Reset to Pending')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Reset to Pending Review')
                ->modalDescription(
                    'This moves the application back to pending status. '
                    . 'The retailer will lose catalogue access until re-approved.'
                )
                ->modalSubmitActionLabel('Reset')
                ->visible(fn (): bool => $this->record->isRejected() || $this->record->isApproved())
                ->action(function (): void {
                    $this->record->update([
                        'kyc_status'           => 'pending',
                        'kyc_rejection_reason' => null,
                    ]);

                    Notification::make()
                        ->title('Reset to Pending')
                        ->body("{$this->record->business_name} is now back in the review queue.")
                        ->info()
                        ->send();

                    $this->redirect(RetailerResource::getUrl('view', ['record' => $this->record]));
                }),

        ];
    }
}
