<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $navigationGroup = 'Retailer Relations';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationBadge(): ?string
    {
        // Count total unread messages from retailers across all conversations
        $unread = \App\Models\Message::where('sender_type', 'retailer')
            ->whereNull('read_at')
            ->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('retailer.user.name')
                    ->label('Retailer')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('retailer.business_name')
                    ->label('Business')
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('product.name_en')
                    ->label('Product')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Township Store')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('unread_count')
                    ->label('Unread')
                    ->getStateUsing(fn (Conversation $r) => $r->unreadByStore())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'open'   => 'success',
                        'closed' => 'gray',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Last Message')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'closed' => 'Closed']),

                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Township Store')
                    ->relationship('store', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open Chat'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view'  => Pages\ViewConversation::route('/{record}'),
        ];
    }
}
