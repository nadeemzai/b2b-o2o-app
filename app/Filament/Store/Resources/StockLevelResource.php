<?php

namespace App\Filament\Store\Resources;

use App\Filament\Store\Resources\StockLevelResource\Pages;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\StoreStaff;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockLevelResource extends Resource
{
    protected static ?string $model = StockLevel::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Stock Levels';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $staff = StoreStaff::where('user_id', Auth::id())->where('is_active', true)->first();
        return parent::getEloquentQuery()
            ->when($staff, fn ($q) => $q->where('store_id', $staff->store_id))
            ->with(['product.category']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name_en')->label('Product')->searchable()->sortable(),
                TextColumn::make('product.category.name')->label('Category'),
                TextColumn::make('qty_on_hand')->label('On Hand')->sortable(),
                TextColumn::make('qty_reserved')->label('Reserved')->sortable(),
                TextColumn::make('qty_available')->label('Available')->sortable()
                    ->color(fn (StockLevel $r): string => $r->qty_available <= 10 ? 'danger' : 'success'),
                TextColumn::make('low_stock')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (StockLevel $r): string => $r->qty_available <= 10 ? 'Low Stock' : 'OK')
                    ->color(fn (string $state): string => $state === 'Low Stock' ? 'danger' : 'success'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(fn () => \App\Models\Category::orderBy('name')->pluck('name', 'id')->toArray())
                    ->label('Category')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder =>
                        isset($data['value']) && $data['value']
                            ? $query->whereHas('product', fn ($q) => $q->where('category_id', $data['value']))
                            : $query
                    ),
            ])
            ->actions([
                Action::make('inbound')
                    ->label('Record Inbound')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        TextInput::make('qty')->label('Qty Received')->numeric()->required()->minValue(1),
                        Textarea::make('note')->label('Note')->rows(2),
                    ])
                    ->action(function (StockLevel $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            StockLevel::lockForUpdate()->find($record->id)->increment('qty_on_hand', (int) $data['qty']);
                            StockMovement::create([
                                'store_id'            => $record->store_id,
                                'product_id'          => $record->product_id,
                                'type'                => 'inbound',
                                'qty'                 => (int) $data['qty'],
                                'note'                => $data['note'] ?? null,
                                'created_by_user_id'  => Auth::id(),
                            ]);
                        });
                        Notification::make()->title('Stock updated')->success()->send();
                    }),
                Action::make('adjust')
                    ->label('Adjust')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        TextInput::make('delta')->label('Adjustment (+/-)')->numeric()->required()
                            ->helperText('Positive to add, negative to remove.'),
                        Textarea::make('reason')->label('Reason')->required()->rows(2),
                    ])
                    ->action(function (StockLevel $record, array $data): void {
                        $delta = (int) $data['delta'];
                        DB::transaction(function () use ($record, $delta, $data) {
                            $fresh = StockLevel::lockForUpdate()->find($record->id);
                            $newQty = $fresh->qty_on_hand + $delta;
                            if ($newQty < 0 || $newQty < $fresh->qty_reserved) {
                                throw new \Exception('Adjustment would result in negative available stock.');
                            }
                            $fresh->update(['qty_on_hand' => $newQty]);
                            StockMovement::create([
                                'store_id'            => $fresh->store_id,
                                'product_id'          => $fresh->product_id,
                                'type'                => 'adjustment',
                                'qty'                 => $delta,
                                'note'                => $data['reason'],
                                'created_by_user_id'  => Auth::id(),
                            ]);
                        });
                        Notification::make()->title('Adjustment recorded')->success()->send();
                    }),
            ])
            ->defaultSort('qty_available', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockLevels::route('/'),
        ];
    }
}
