<?php

namespace App\Filament\Huashu\Resources;

use App\Filament\Huashu\Resources\CategoryResource\Pages;
use App\Filament\Huashu\Resources\CategoryResource\RelationManagers\CommissionsRelationManager;
use App\Models\Category;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?int $navigationSort = 1;

    // ── Read-only for Huashu: only OZ Admin (Admin panel) manages categories ──
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
    public static function canForceDelete(Model $record): bool { return false; }
    public static function canForceDeleteAny(): bool { return false; }
    public static function canRestore(Model $record): bool { return false; }
    public static function canRestoreAny(): bool { return false; }

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListCategories::route('/'),
            'view'  => Pages\ViewCategory::route('/{record}'),
        ];
    }
}
