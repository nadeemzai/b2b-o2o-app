<?php

namespace App\Livewire\Retailer\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;

class ProductChat extends Component
{
    public Product $product;

    public bool   $open    = false;
    public string $newMessage = '';

    public ?Conversation $conversation = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->loadConversation();
    }

    // ── Open / close panel ────────────────────────────────────────────────

    public function openChat(): void
    {
        $this->open = true;
        $this->loadConversation();
        $this->markRead();
    }

    public function closeChat(): void
    {
        $this->open = false;
    }

    // ── Polling refresh (every 5s while panel is open) ────────────────────

    #[On('refresh-chat')]
    public function refresh(): void
    {
        if ($this->open) {
            $this->loadConversation();
        }
    }

    // ── Send a message ────────────────────────────────────────────────────

    public function sendMessage(): void
    {
        $this->newMessage = trim($this->newMessage);

        if ($this->newMessage === '') {
            return;
        }

        $retailer = auth('retailer')->user()->retailerProfile;
        $user     = auth('retailer')->user();

        // Create conversation if it doesn't exist yet
        if (! $this->conversation) {
            $this->conversation = Conversation::create([
                'product_id'  => $this->product->id,
                'retailer_id' => $retailer->id,
                'store_id'    => $retailer->store_id,
                'status'      => 'open',
            ]);
        }

        Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id'       => $user->id,
            'sender_type'     => 'retailer',
            'body'            => $this->newMessage,
        ]);

        // Update last_message_at on the conversation
        $this->conversation->update(['last_message_at' => now()]);

        $this->newMessage = '';
        $this->loadConversation();
        $this->markRead();
    }

    // ── Unread badge count (called from blade) ────────────────────────────

    public function getUnreadCount(): int
    {
        return $this->conversation?->unreadByRetailer() ?? 0;
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function loadConversation(): void
    {
        $retailer = auth('retailer')->user()->retailerProfile;

        $this->conversation = Conversation::with(['messages.sender'])
            ->where('product_id', $this->product->id)
            ->where('retailer_id', $retailer->id)
            ->first();
    }

    private function markRead(): void
    {
        if (! $this->conversation) {
            return;
        }

        // Mark all store_staff messages as read when retailer opens the chat
        $this->conversation->messages()
            ->where('sender_type', 'store_staff')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.retailer.chat.product-chat');
    }
}
