<?php

namespace App\Filament\Huashu\Resources\CategoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissions';
    protected static ?string $title       = 'OZ Commission Rates';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commission_rate')
            ->defaultSort('effective_from', 'desc')
            ->columns([
                TextColumn::make('commission_rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(fn ($state): string => round($state * 100, 2) . '%')
                    ->sortable(),
                TextColumn::make('effective_from')
                    ->label('Effective From')
                    ->date()
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Set By (OZ Admin)')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
