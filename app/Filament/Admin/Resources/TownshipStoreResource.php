<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TownshipStoreResource\Pages;
use App\Models\TownshipStore;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TownshipStoreResource extends Resource
{
    protected static ?string $model = TownshipStore::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Township Stores';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Store Details')->schema([
                TextInput::make('name')->required()->maxLength(150),
                TextInput::make('city')->required()->maxLength(100),
                TextInput::make('address')->required()->maxLength(500),
                TextInput::make('phone')->maxLength(20),
                Select::make('manager_user_id')
                    ->label('Manager')
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('is_active')->default(true)->label('Active'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->sortable(),
                TextColumn::make('address')->limit(40)->toggleable(),
                TextColumn::make('phone'),
                TextColumn::make('manager.name')->label('Manager'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('retailers_count')
                    ->label('Retailers')
                    ->counts('retailers'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index'  => Pages\ListTownshipStores::route('/'),
            'create' => Pages\CreateTownshipStore::route('/create'),
            'edit'   => Pages\EditTownshipStore::route('/{record}/edit'),
        ];
    }
}
