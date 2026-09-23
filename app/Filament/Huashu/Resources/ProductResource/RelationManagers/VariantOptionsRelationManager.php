<?php

namespace App\Filament\Huashu\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariantType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'variantTypes';
    protected static ?string $title = 'Variant Options';

    // We navigate via type → options, so we expose a nested view.
    // Actually, the cleaner approach is to register a separate RM for each
    // ProductVariantType's options. But for simplicity we'll embed the options
    // editor inside VariantTypesRelationManager via a repeater instead.
    // This file is kept as a placeholder; see VariantTypesRelationManager.

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([]);
    }
}
