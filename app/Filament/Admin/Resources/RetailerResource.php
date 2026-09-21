<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RetailerResource\Pages;
use App\Models\Retailer;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RetailerResource extends Resource
{
    protected static ?string $model = Retailer::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'KYC & Retailers';
    protected static ?string $navigationLabel = 'Retailers / KYC';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Business Information')->schema([
                TextInput::make('business_name')->disabled(),
                TextInput::make('cnic')->disabled()->label('CNIC'),
                TextInput::make('phone')->disabled(),
                TextInput::make('address')->disabled(),
                TextInput::make('ntn')->disabled()->label('NTN'),
                TextInput::make('strn')->disabled()->label('STRN'),
            ])->columns(2),
            Section::make('KYC Status')->schema([
                Placeholder::make('kyc_status')
                    ->content(fn (Retailer $record): string => ucfirst($record->kyc_status)),
                Placeholder::make('kyc_rejection_reason')
                    ->content(fn (Retailer $record): string => $record->kyc_rejection_reason ?? '—')
                    ->label('Rejection Reason'),
            ]),
        ]);
    }

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
                    }),
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
                    ->options(fn () => \App\Models\TownshipStore::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Store'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve KYC')
                    ->modalDescription('This will approve the retailer and allow them to place orders.')
                    ->visible(fn (Retailer $record): bool => $record->kyc_status === 'pending')
                    ->action(function (Retailer $record): void {
                        $record->update(['kyc_status' => 'approved', 'kyc_rejection_reason' => null]);
                        Notification::make()->title('Retailer approved successfully')->success()->send();
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
