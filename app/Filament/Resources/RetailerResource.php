<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RetailerResource\Pages;
use App\Models\Retailer;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RetailerResource extends Resource
{
    protected static ?string $model = Retailer::class;

    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'KYC Applications';
    protected static ?string $navigationGroup = 'Retailers';
    protected static ?int    $navigationSort  = 10;

    // ──────────────────────────────────────────────
    // No create/edit — KYC is retailer-initiated
    // ──────────────────────────────────────────────

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ──────────────────────────────────────────────
    // Infolist (View page)
    // ──────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Business Details ──────────────────────────────────────────
            Infolists\Components\Section::make('Business Details')
                ->schema([
                    Infolists\Components\TextEntry::make('business_name')
                        ->label('Business Name')
                        ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                    Infolists\Components\TextEntry::make('cnic')
                        ->label('CNIC'),

                    Infolists\Components\TextEntry::make('phone')
                        ->label('Phone'),

                    Infolists\Components\TextEntry::make('ntn')
                        ->label('NTN')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('strn')
                        ->label('STRN')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('store.name')
                        ->label('Assigned Store'),

                    Infolists\Components\TextEntry::make('address')
                        ->label('Address')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            // ── Account ───────────────────────────────────────────────────
            Infolists\Components\Section::make('User Account')
                ->schema([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Full Name'),

                    Infolists\Components\TextEntry::make('user.email')
                        ->label('Email')
                        ->copyable(),
                ])
                ->columns(2),

            // ── KYC Documents ─────────────────────────────────────────────
            Infolists\Components\Section::make('KYC Documents')
                ->description('Click any image to open it full-size in a new tab.')
                ->schema([
                    Infolists\Components\ImageEntry::make('kyc_cnic_front')
                        ->label('CNIC Front')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            isset($record->kyc_documents['cnic_front'])
                                ? route('admin.kyc.document', [$record->id, 'cnic_front'])
                                : null
                        )
                        ->height(220)
                        ->extraImgAttributes([
                            'class'  => 'object-contain border rounded-lg bg-gray-50 cursor-pointer',
                            'onclick' => 'window.open(this.src,"_blank")',
                        ])
                        ->defaultImageUrl('https://placehold.co/400x220/f3f4f6/94a3b8?text=Not+uploaded'),

                    Infolists\Components\ImageEntry::make('kyc_cnic_back')
                        ->label('CNIC Back')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            isset($record->kyc_documents['cnic_back'])
                                ? route('admin.kyc.document', [$record->id, 'cnic_back'])
                                : null
                        )
                        ->height(220)
                        ->extraImgAttributes([
                            'class'  => 'object-contain border rounded-lg bg-gray-50 cursor-pointer',
                            'onclick' => 'window.open(this.src,"_blank")',
                        ])
                        ->defaultImageUrl('https://placehold.co/400x220/f3f4f6/94a3b8?text=Not+uploaded'),

                    Infolists\Components\ImageEntry::make('kyc_business_doc')
                        ->label('Business Registration')
                        ->getStateUsing(fn (Retailer $record): ?string =>
                            isset($record->kyc_documents['business_doc'])
                                ? route('admin.kyc.document', [$record->id, 'business_doc'])
                                : null
                        )
                        ->height(220)
                        ->extraImgAttributes([
                            'class'  => 'object-contain border rounded-lg bg-gray-50 cursor-pointer',
                            'onclick' => 'window.open(this.src,"_blank")',
                        ])
                        ->defaultImageUrl('https://placehold.co/400x220/f3f4f6/94a3b8?text=Not+uploaded'),
                ])
                ->columns(3),

            // ── KYC Status ────────────────────────────────────────────────
            Infolists\Components\Section::make('KYC Status')
                ->schema([
                    Infolists\Components\TextEntry::make('kyc_status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'approved' => 'success',
                            'pending'  => 'warning',
                            'rejected' => 'danger',
                            default    => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime('d M Y, H:i'),

                    Infolists\Components\TextEntry::make('kyc_rejection_reason')
                        ->label('Rejection Reason')
                        ->visible(fn (Retailer $record): bool => $record->isRejected())
                        ->columnSpanFull()
                        ->color('danger')
                        ->icon('heroicon-o-exclamation-triangle'),
                ])
                ->columns(2),

        ]);
    }

    // ──────────────────────────────────────────────
    // Table (List page)
    // ──────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cnic')
                    ->label('CNIC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kyc_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    // ──────────────────────────────────────────────
    // Pages
    // ──────────────────────────────────────────────

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetailers::route('/'),
            'view'  => Pages\ViewRetailer::route('/{record}'),
        ];
    }
}
