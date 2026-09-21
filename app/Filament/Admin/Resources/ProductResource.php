<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Product Details')->schema([
                TextInput::make('name_en')->label('Name (EN)')->required()->maxLength(200),
                TextInput::make('name_ur')->label('Name (UR)')->maxLength(200),
                TextInput::make('sku')->label('SKU')->maxLength(100)->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('unit')->maxLength(20)->default('pcs'),
                TextInput::make('pieces_per_carton')->numeric()->minValue(1)->default(1),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
            Section::make('Description')->schema([
                Textarea::make('description_en')->label('Description (EN)')->rows(3),
                Textarea::make('description_ur')->label('Description (UR)')->rows(3),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_en')->label('Name')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('category.name')->label('Category')->sortable(),
                TextColumn::make('unit'),
                TextColumn::make('pieces_per_carton')->label('Pcs/Carton'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(fn () => \App\Models\Category::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Category'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
