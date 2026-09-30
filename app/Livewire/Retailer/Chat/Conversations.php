<?php

namespace App\Livewire\Retailer\Chat;

use App\Models\Conversation;
use Livewire\Component;

class Conversations extends Component
{
    public function render(): \Illuminate\View\View
    {
        $retailer = auth('retailer')->user()->retailerProfile;

        $conversations = Conversation::with([
            'product',
            'product.images',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ])
            ->where('retailer_id', $retailer->id)
            ->latest('last_message_at')
            ->get();

        return view('livewire.retailer.chat.conversations', compact('conversations'))
            ->layout('layouts.retailer');
    }
}
