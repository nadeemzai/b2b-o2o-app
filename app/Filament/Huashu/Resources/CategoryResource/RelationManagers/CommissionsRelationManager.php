<?php

namespace App\Filament\Huashu\Resources\CategoryResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissions';
    protected static ?string $title       = 'Commission Rates';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('commission_rate')
                ->label('Commission Rate')
                ->numeric()
                ->minValue(0)
                ->maxValue(1)
                ->step(0.0001)
                ->suffix('%')
                ->formatStateUsing(fn ($state) => $state !== null ? round($state * 100, 4) : null)
                ->dehydrateStateUsing(fn ($state) => $state !== null ? round($state / 100, 6) : null)
                ->helperText('Enter as a percentage, e.g. 15 = 15%. Stored as 0.15.')
                ->required(),
            DatePicker::make('effective_from')
                ->label('Effective From')
                ->default(now())
                ->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commission_rate')
            ->defaultSort('effective_from', 'desc')
            ->columns([
                TextColumn::make('commission_rate')
                    ->label('Rate')
                    ->formatStateUsing(fn ($state): string => round($state * 100, 2) . '%')
                    ->sortable(),
                TextColumn::make('effective_from')
                    ->label('Effective From')
                    ->date()
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Set By')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
