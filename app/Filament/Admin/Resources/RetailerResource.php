<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RetailerResource\Pages;
use App\Models\Retailer;
use App\Models\TownshipStore;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class RetailerResource extends Resource
{
    protected static ?string $model = Retailer::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'KYC & Retailers';
    protected static ?string $navigationLabel = 'Retailers / KYC';
    protected static ?int $navigationSort = 1;

    // ── Infolist (ViewRetailer page) ────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // Business profile
            Section::make('Business Information')
                ->columns(3)
                ->schema([
                    TextEntry::make('business_name')->label('Business Name'),
                    TextEntry::make('user.email')->label('Email'),
                    TextEntry::make('phone')->label('Phone'),
                    TextEntry::make('cnic')->label('CNIC'),
                    TextEntry::make('ntn')->label('NTN')->placeholder('—'),
                    TextEntry::make('strn')->label('STRN')->placeholder('—'),
                    TextEntry::make('address')->label('Address')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('store.name')->label('Assigned Store')->placeholder('—'),
                    TextEntry::make('created_at')->label('Registered')->dateTime(),
                ]),

            // KYC status
            Section::make('KYC Status')
                ->columns(2)
                ->schema([
                    TextEntry::make('kyc_status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'pending'  => 'warning',
                            default    => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    TextEntry::make('kyc_rejection_reason')
                        ->label('Rejection Reason')
                        ->placeholder('—')
                        ->visible(fn (Retailer $record): bool => $record->kyc_status === 'rejected'),
                ]),

            // KYC Document images — served via auth-gated controller
            Section::make('KYC Documents')
                ->description('Documents are served securely and are only visible to admins.')
                ->columns(3)
                ->schema([
                    ImageEntry::make('kyc_cnic_front')
                        ->label('CNIC Front')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            ($record->kyc_documents['cnic_front'] ?? null)
                                ? route('admin.kyc.document', [$record, 'cnic_front'])
                                : null
                        )
                        ->height(200)
                        ->extraImgAttributes(['class' => 'rounded object-contain'])
                        ->placeholder('Not uploaded'),

                    ImageEntry::make('kyc_cnic_back')
                        ->label('CNIC Back')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            ($record->kyc_documents['cnic_back'] ?? null)
                                ? route('admin.kyc.document', [$record, 'cnic_back'])
                                : null
                        )
                        ->height(200)
                        ->extraImgAttributes(['class' => 'rounded object-contain'])
                        ->placeholder('Not uploaded'),

                    ImageEntry::make('kyc_business_doc')
                        ->label('Business Document')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            ($record->kyc_documents['business_doc'] ?? null)
                                ? route('admin.kyc.document', [$record, 'business_doc'])
                                : null
                        )
                        ->height(200)
                        ->extraImgAttributes(['class' => 'rounded object-contain'])
                        ->placeholder('Not uploaded'),
                ]),
        ]);
    }

    // ── Table (ListRetailers page) ──────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('cnic')->label('CNIC'),
                TextColumn::make('kyc_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending'  => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('store.name')->label('Store')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('kyc_status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('store_id')
                    ->options(fn () => TownshipStore::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Store'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Quick approve from table row
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve KYC')
                    ->modalDescription('Approve this retailer and allow them to place orders.')
                    ->visible(fn (Retailer $record): bool => $record->kyc_status === 'pending')
                    ->action(function (Retailer $record): void {
                        $record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                        Notification::make()->title('Retailer approved')->success()->send();
                    }),

                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Retailer $record): bool => $record->kyc_status === 'pending')
                    ->form([
                        Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(1000)
                            ->placeholder('Explain why the KYC documents were rejected...'),
                    ])
                    ->action(function (Retailer $record, array $data): void {
                        $record->update([
                            'kyc_status'           => 'rejected',
                            'kyc_rejection_reason' => $data['reason'],
                        ]);
                        Notification::make()->title('Retailer rejected')->warning()->send();
                    }),

                Action::make('re_approve')
                    ->label('Re-approve')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Retailer $record): bool => $record->kyc_status === 'rejected')
                    ->action(function (Retailer $record): void {
                        $record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                        Notification::make()->title('Retailer re-approved')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetailers::route('/'),
            'view'  => Pages\ViewRetailer::route('/{record}'),
        ];
    }
}
