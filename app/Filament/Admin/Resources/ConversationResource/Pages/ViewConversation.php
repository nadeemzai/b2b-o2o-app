<?php

namespace App\Filament\Admin\Resources\ConversationResource\Pages;

use App\Filament\Admin\Resources\ConversationResource;
use App\Models\Message;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected static string $view = 'filament.admin.resources.conversation.view';

    public string $replyBody = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Mark retailer messages as read when admin opens the conversation
        $this->record->messages()
            ->where('sender_type', 'retailer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendReply(): void
    {
        $body = trim($this->replyBody);

        if ($body === '') {
            return;
        }

        Message::create([
            'conversation_id' => $this->record->id,
            'sender_id'       => auth()->id(),
            'sender_type'     => 'store_staff',
            'body'            => $body,
        ]);

        $this->record->update(['last_message_at' => now()]);
        $this->replyBody = '';

        // Refresh messages
        $this->record->load('messages.sender');

        Notification::make()
            ->title('Reply sent')
            ->success()
            ->send();
    }

    public function closeConversation(): void
    {
        $this->record->update(['status' => 'closed']);

        Notification::make()
            ->title('Conversation closed')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close')
                ->label('Close Conversation')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->action('closeConversation')
                ->visible(fn () => $this->record->status === 'open'),
        ];
    }
}
