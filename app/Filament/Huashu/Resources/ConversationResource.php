<?php

namespace App\Filament\Huashu\Resources;

use App\Filament\Huashu\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use App\Models\Message;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Retailer Messages';
    protected static ?string $navigationGroup = 'Support';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationBadge(): ?string
    {
        // Unread retailer messages in conversations claimed by Huashu OR unclaimed
        $unread = Message::where('sender_type', 'retailer')
            ->whereNull('read_at')
            ->whereHas('conversation', fn ($q) =>
                $q->where('claimed_by', 'huashu')->orWhereNull('claimed_by')
            )
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
            ->defaultSort('last_message_at', 'desc')
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

                Tables\Columns\TextColumn::make('unread_count')
                    ->label('Unread')
                    ->getStateUsing(fn (Conversation $r) => $r->unreadByStore())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignment('center'),

                // Show who has claimed each conversation
                Tables\Columns\TextColumn::make('claimed_by')
                    ->label('Owner')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'oz_admin' => 'OZ Admin',
                        'huashu'   => 'Huashu (You)',
                        null       => 'Unclaimed',
                    })
                    ->color(fn ($state) => match ($state) {
                        'oz_admin' => 'warning',   // orange — another team, read-only
                        'huashu'   => 'success',   // green — owned by us
                        null       => 'gray',
                    }),

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
            ->filters([
                Tables\Filters\SelectFilter::make('claimed_by')
                    ->label('Owner')
                    ->options([
                        'huashu'   => 'Huashu (Ours)',
                        'oz_admin' => 'OZ Admin (Locked)',
                        ''         => 'Unclaimed',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'closed' => 'Closed']),
            ])
            ->recordUrl(fn (Conversation $r) =>
                Pages\ViewConversation::getUrl(['record' => $r])
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view'  => Pages\ViewConversation::route('/{record}'),
        ];
    }
}
