<?php

namespace App\Filament\Huashu\Resources\ConversationResource\Pages;

use App\Filament\Huashu\Resources\ConversationResource;
use App\Models\Message;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected static string $view = 'filament.huashu.resources.conversation.view';

    /** This page represents the Huashu panel. */
    protected string $claimPanel = 'huashu';

    public string $replyBody = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Mark retailer messages as read when Huashu admin opens the conversation
        $this->record->messages()
            ->where('sender_type', 'retailer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    // ── Reply ─────────────────────────────────────────────────────────────

    public function sendReply(): void
    {
        $body = trim($this->replyBody);

        if ($body === '') {
            return;
        }

        $conversation = $this->record;

        // Block if OZ Admin has already claimed this thread
        if ($conversation->claimed_by !== null && ! $conversation->isClaimedBy($this->claimPanel)) {
            Notification::make()
                ->title('Conversation locked')
                ->body("This conversation is already being handled by the {$conversation->claimedByLabel()}. Only they can reply.")
                ->warning()
                ->duration(6000)
                ->send();
            return;
        }

        // Auto-claim on first reply (first-writer wins)
        $conversation->claimFor($this->claimPanel, auth()->id());
        $conversation->refresh();

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => auth()->id(),
            'sender_type'     => 'store_staff',
            'body'            => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $this->replyBody = '';

        $this->record->load('messages.sender', 'claimedByUser');

        Notification::make()
            ->title('Reply sent')
            ->success()
            ->send();
    }

    // ── Close ─────────────────────────────────────────────────────────────

    public function closeConversation(): void
    {
        $this->record->update(['status' => 'closed']);

        Notification::make()
            ->title('Conversation closed')
            ->success()
            ->send();
    }

    // ── Release claim ─────────────────────────────────────────────────────

    public function releaseClaim(): void
    {
        $this->record->update([
            'claimed_by'         => null,
            'claimed_by_user_id' => null,
            'claimed_at'         => null,
        ]);

        $this->record->refresh();

        Notification::make()
            ->title('Claim released')
            ->body('OZ Admin team can now reply to this conversation.')
            ->success()
            ->send();
    }

    // ── Header actions ────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('release_claim')
                ->label('Release to OZ Admin')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will allow the OZ Admin team to take over and reply. Are you sure?')
                ->action('releaseClaim')
                ->visible(fn () => $this->record->isClaimedBy($this->claimPanel)),

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
