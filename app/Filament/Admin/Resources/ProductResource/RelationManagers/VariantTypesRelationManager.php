<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'variantTypes';
    protected static ?string $title = 'Product Variants';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Variant Dimension')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Dimension Name')
                    ->placeholder('e.g. Color, Size, Fabric Type')
                    ->required()
                    ->maxLength(80)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('display_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->columnSpan(1),
            ])->columns(3),

            Forms\Components\Section::make('Options')->schema([
                Forms\Components\Repeater::make('options')
                    ->relationship('options')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Option Value')
                            ->placeholder('e.g. Red, XL, Cotton')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('sku_suffix')
                            ->label('SKU Suffix')
                            ->placeholder('e.g. -RED')
                            ->maxLength(30)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('price_adjustment_pkr')
                            ->label('Price Adjustment (PKR)')
                            ->numeric()
                            ->default(0)
                            ->step(1)
                            ->prefix('PKR')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('display_order')
                            ->label('Order')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(9)
                    ->itemLabel(fn (array $state): ?string => $state['value'] ?? null)
                    ->collapsible()
                    ->orderColumn('display_order')
                    ->addActionLabel('Add Option')
                    ->reorderable(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Dimension')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('options')
                    ->label('Values')
                    ->formatStateUsing(function ($record) {
                        return $record->activeOptions->pluck('value')->join(', ');
                    }),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Variant Dimension'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
