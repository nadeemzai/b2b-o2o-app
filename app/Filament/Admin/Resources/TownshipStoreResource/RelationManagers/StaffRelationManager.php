<?php

namespace App\Filament\Admin\Resources\TownshipStoreResource\RelationManagers;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';
    protected static ?string $title = 'Staff Members';
    protected static ?string $modelLabel = 'Staff Member';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('User Account')
                ->options(
                    // Show store_staff users not already assigned to this store
                    fn () => User::where('role', 'store_staff')
                        ->whereNotIn('id', $this->getOwnerRecord()->staff()->pluck('user_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->searchable()
                ->preload()
                ->required()
                ->hiddenOn('edit'),

            Select::make('position')
                ->options([
                    'manager'  => 'Manager',
                    'operator' => 'Operator',
                    'rider'    => 'Rider',
                ])
                ->required(),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('position')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manager'  => 'danger',
                        'operator' => 'info',
                        'rider'    => 'warning',
                        default    => 'gray',
                    }),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Assigned')->toggleable(),
            ])
            ->headerActions([
                // Assign an existing store_staff user
                CreateAction::make()
                    ->label('Assign Staff')
                    ->modalHeading('Assign Staff Member'),

                // Create a brand-new user AND assign them in one step
                Action::make('create_and_assign')
                    ->label('New Staff + Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()->maxLength(150),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->email()->required()
                            ->unique('users', 'email'),
                        \Filament\Forms\Components\TextInput::make('password')
                            ->password()->required()->minLength(8)
                            ->helperText('Minimum 8 characters'),
                        Select::make('position')
                            ->options([
                                'manager'  => 'Manager',
                                'operator' => 'Operator',
                                'rider'    => 'Rider',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        DB::transaction(function () use ($data) {
                            $user = User::create([
                                'name'              => $data['name'],
                                'email'             => $data['email'],
                                'password'          => Hash::make($data['password']),
                                'role'              => 'store_staff',
                                'is_active'         => true,
                                'email_verified_at' => now(),
                            ]);

                            $this->getOwnerRecord()->staff()->create([
                                'user_id'   => $user->id,
                                'position'  => $data['position'],
                                'is_active' => true,
                            ]);
                        });

                        Notification::make()->title('Staff member created and assigned')->success()->send();
                    }),
            ])
            ->actions([
                EditAction::make()->modalHeading('Edit Staff Assignment'),
                Action::make('toggle_active')
                    ->label(fn (Model $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Model $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Model $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Staff member activated' : 'Staff member deactivated')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()->label('Remove from Store'),
            ]);
    }
}
