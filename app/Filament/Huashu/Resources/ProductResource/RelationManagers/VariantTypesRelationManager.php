<?php

namespace App\Filament\Huashu\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'variantTypes';

    protected static ?string $title = 'Product Variants';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Variant Dimension')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Dimension Name (e.g. Color, Size, Fabric)')
                    ->required()
                    ->maxLength(80)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('display_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0),
            ]),

            Forms\Components\Section::make('Options')->schema([
                Forms\Components\Repeater::make('options')
                    ->relationship('options')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Option (e.g. Red, XL, Cotton)')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('price_adjustment_pkr')
                            ->label('Price Adjustment (PKR)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('display_order')
                            ->label('Order')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(4)
                    ->addActionLabel('Add Option')
                    ->defaultItems(0),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Variant Dimension'),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
            ])
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
