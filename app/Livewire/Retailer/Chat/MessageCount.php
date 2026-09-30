<?php

namespace App\Livewire\Retailer\Chat;

use App\Models\Message;
use Livewire\Attributes\On;
use Livewire\Component;

class MessageCount extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = $this->unreadCount();
    }

    /** Refresh when the ProductChat component fires 'chat-messages-read' */
    #[On('chat-messages-read')]
    public function refresh(): void
    {
        $this->count = $this->unreadCount();
    }

    private function unreadCount(): int
    {
        if (! auth('retailer')->check()) {
            return 0;
        }

        $retailer = auth('retailer')->user()->retailerProfile;

        if (! $retailer) {
            return 0;
        }

        return Message::whereHas(
            'conversation',
            fn ($q) => $q->where('retailer_id', $retailer->id)
        )
            ->where('sender_type', 'store_staff')
            ->whereNull('read_at')
            ->count();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.retailer.chat.message-count');
    }
}
