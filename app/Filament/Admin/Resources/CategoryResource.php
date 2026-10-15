<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers\CommissionsRelationManager;
use App\Models\Category;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Category Names')->schema([
                TextInput::make('name')
                    ->label('Name (English)')
                    ->required()
                    ->maxLength(150),
                TextInput::make('name_zh')
                    ->label('Name (Chinese 中文)')
                    ->maxLength(150)
                    ->placeholder('e.g. 婴儿产品'),
                TextInput::make('slug')->maxLength(150)->unique(ignoreRecord: true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('products_count')->label('Products')->counts('products'),
                TextColumn::make('activeCommission.commission_rate')
                    ->label('Current Commission')
                    ->formatStateUsing(fn ($state): string => round((float) $state * 100, 2) . '%')
                    ->placeholder('Not set')
                    ->badge()
                    ->color(fn ($state): string => $state !== null ? 'success' : 'warning'),
                TextColumn::make('commissions_count')
                    ->label('Rate History')
                    ->counts('commissions')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),   // soft delete (sets deleted_at)
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    // No ForceDelete actions — categories are strictly soft deleted.
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Let OZ Admin see/restore soft-deleted categories via the Trashed filter
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            CommissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
