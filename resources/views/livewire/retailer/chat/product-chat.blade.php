{{--
  ProductChat component
  Embeds on the retailer product detail page.
  Shows: unread badge on trigger button, slide-over chat panel.
--}}
<div x-data="{ polling: null }"
     x-init="
        $watch('$wire.open', val => {
            if (val) {
                polling = setInterval(() => $wire.refresh(), 5000);
                $nextTick(() => scrollBottom());
            } else {
                clearInterval(polling);
            }
        });
     ">

    {{-- ── TRIGGER BUTTON ───────────────────────────────────────────────── --}}
    <div class="mt-4">
        @php
            $store        = $product->storePrices()->with('store')->first()?->store
                         ?? auth('retailer')->user()->retailerProfile?->store;
            $storeName    = $store?->name ?? 'Township Store';
            $storeCity    = $store?->city ?? '';
            $unread       = $this->getUnreadCount();
        @endphp

        {{-- Seller info card --}}
        <div class="border border-gray-200 rounded-xl p-4 bg-white flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $storeName }}</p>
                @if($storeCity)
                    <p class="text-xs text-gray-500">{{ $storeCity }}</p>
                @endif
            </div>
            <button wire:click="openChat"
                    class="relative inline-flex items-center gap-2 bg-brand text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Ask Seller
                @if($unread > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                        {{ $unread }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    {{-- ── SLIDE-OVER PANEL ─────────────────────────────────────────────── --}}
    {{--
        @teleport('body') renders this outside the .rpdp-right-sticky overflow
        container so `fixed` positioning works relative to the true viewport.
        Without this, overflow-y:auto on the sticky purchase panel clips/contains
        the fixed overlay and makes it appear collapsed inside the panel.
    --}}
    @if($open)
    @teleport('body')
    <div class="fixed inset-0 z-[200] flex" id="chat-slide-over">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" wire:click="closeChat"></div>

        {{-- Panel --}}
        <div class="absolute right-0 bottom-0 h-[calc(50vh+205px)] w-full max-w-md bg-white shadow-2xl flex flex-col rounded-[40px] opacity-90">

            {{-- Header --}}
            <div class="flex items-center gap-3 px-4 py-4 border-b border-gray-100 bg-white flex-shrink-0">
                <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $storeName }}</p>
                    <p class="text-xs text-gray-400 truncate">Re: {{ $product->name_en }}</p>
                </div>
                <button wire:click="closeChat" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Product context strip --}}
            <div class="flex items-center gap-3 px-4 py-2 bg-orange-50 border-b border-orange-100 flex-shrink-0">
                @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" class="w-10 h-10 rounded-lg object-cover">
                @endif
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-700 truncate">{{ $product->name_en }}</p>
                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                </div>
            </div>

            {{-- Messages area --}}
            <div id="chat-messages"
                 class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">

                @if(! $conversation || $conversation->messages->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm text-gray-400">No messages yet.<br>Start the conversation below.</p>
                    </div>
                @else
                    @foreach($conversation->messages as $msg)
                        @php $mine = $msg->isFromRetailer(); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%]">
                                <div class="{{ $mine
                                        ? 'bg-brand text-white rounded-tl-2xl rounded-tr-sm rounded-bl-2xl rounded-br-2xl'
                                        : 'bg-white text-gray-800 rounded-tl-sm rounded-tr-2xl rounded-bl-2xl rounded-br-2xl border border-gray-200'
                                    }} px-4 py-2 text-sm shadow-sm">
                                    {{ $msg->body }}
                                </div>
                                <p class="text-xs text-gray-400 mt-1 {{ $mine ? 'text-right' : 'text-left' }}">
                                    {{ $msg->created_at->format('M j, g:i a') }}
                                    @if($mine && $msg->read_at)
                                        · <span class="text-green-500">Read</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Input area --}}
            <div class="border-t border-gray-200 px-4 py-3 bg-white flex-shrink-0">
                <div class="flex gap-2 items-end">
                    <textarea wire:model="newMessage"
                              wire:keydown.enter.prevent="sendMessage"
                              placeholder="Type a message…"
                              rows="2"
                              class="flex-1 resize-none border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50 focus:border-brand placeholder-gray-400"></textarea>
                    <button wire:click="sendMessage"
                            wire:loading.attr="disabled"
                            class="bg-brand text-white p-3 rounded-xl hover:bg-orange-600 transition-colors flex-shrink-0 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Enter to send · Store staff will reply within business hours</p>
            </div>
        </div>
    </div>

    <script>
        function scrollBottom() {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }
        document.addEventListener('livewire:updated', scrollBottom);
        scrollBottom();
    </script>
    @endteleport
    @endif
</div>
