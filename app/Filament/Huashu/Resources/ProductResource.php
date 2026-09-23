<?php

namespace App\Filament\Huashu\Resources;

use App\Filament\Huashu\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?int $navigationSort = 2;

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

            Section::make('Huashu Pricing & MOQ')
                ->description('Base price determines the retailer selling price via category commission rate.')
                ->schema([
                    TextInput::make('huashu_base_price_pkr')
                        ->label('Huashu Base Price (PKR)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->prefix('PKR')
                        ->placeholder('e.g. 850.00')
                        ->helperText('The price OZ pays Huashu. Retailer price = this + commission markup.'),
                    TextInput::make('moq')
                        ->label('Min Order Qty (MOQ)')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->integer()
                        ->helperText('Minimum units a retailer must order in one cart line.'),
                ])->columns(2),

            Section::make('Product Image')
                ->schema([
                    FileUpload::make('image_path')
                        ->label('Product Image')
                        ->image()
                        ->disk('public')
                        ->directory('products')
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('800')
                        ->maxSize(2048)
                        ->helperText('Upload a product photo. Max 2MB. Will be resized to 800×800.'),
                ])->columns(1),

            Section::make('Product Variants')
                ->description('Add variant dimensions (e.g. Color, Size, Fabric). Each dimension can have multiple options with optional price adjustments.')
                ->schema([
                    Repeater::make('variantTypes')
                        ->relationship('variantTypes')
                        ->label('Variant Dimensions')
                        ->schema([
                            TextInput::make('name')
                                ->label('Dimension Name')
                                ->required()
                                ->placeholder('e.g. Color, Size, Fabric')
                                ->maxLength(100),
                            TextInput::make('display_order')
                                ->label('Display Order')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Repeater::make('options')
                                ->relationship('options')
                                ->label('Options')
                                ->schema([
                                    TextInput::make('value')
                                        ->label('Option Value')
                                        ->required()
                                        ->placeholder('e.g. Red, Large, Cotton')
                                        ->maxLength(100),
                                    TextInput::make('sku_suffix')
                                        ->label('SKU Suffix')
                                        ->placeholder('e.g. -RED, -L')
                                        ->maxLength(50),
                                    TextInput::make('price_adjustment_pkr')
                                        ->label('Price Adjustment (PKR)')
                                        ->numeric()
                                        ->default(0)
                                        ->step(0.01)
                                        ->prefix('PKR')
                                        ->helperText('+/- amount added to base price for this option'),
                                    TextInput::make('display_order')
                                        ->label('Order')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true),
                                ])
                                ->columns(5)
                                ->addActionLabel('Add Option')
                                ->collapsible()
                                ->defaultItems(1),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add Variant Dimension')
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->size(48),
                TextColumn::make('name_en')->label('Name')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('category.name')->label('Category')->sortable(),
                TextColumn::make('unit'),
                TextColumn::make('pieces_per_carton')->label('Pcs/Carton'),
                TextColumn::make('huashu_base_price_pkr')
                    ->label('Huashu Price (PKR)')
                    ->money('PKR')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('moq')
                    ->label('MOQ')
                    ->placeholder('—')
                    ->sortable(),
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
