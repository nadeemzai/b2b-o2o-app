<x-filament-panels::page>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Product & Retailer info ─────────────────────────── --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Product card --}}
            <x-filament::card>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Product</h3>
                @php $product = $this->record->product; @endphp
                @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}"
                         class="w-full h-40 object-contain rounded-lg bg-gray-50 mb-3">
                @endif
                <p class="font-semibold text-gray-900 text-sm">{{ $product->name_en }}</p>
                <p class="text-xs text-gray-400 mt-1">SKU: {{ $product->sku }}</p>
                <p class="text-xs text-gray-400">MOQ: {{ $product->moq }} {{ $product->unit }}</p>
            </x-filament::card>

            {{-- Retailer card --}}
            <x-filament::card>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Retailer</h3>
                @php $retailer = $this->record->retailer; @endphp
                <p class="font-semibold text-gray-900 text-sm">{{ $retailer->business_name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $retailer->phone }}</p>
                <p class="text-xs text-gray-400">{{ $retailer->address }}</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $retailer->kyc_status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        KYC: {{ ucfirst($retailer->kyc_status) }}
                    </span>
                </div>
            </x-filament::card>

            {{-- Store card --}}
            <x-filament::card>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Township Store</h3>
                @php $store = $this->record->store; @endphp
                <p class="font-semibold text-gray-900 text-sm">{{ $store->name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $store->city }}</p>
                <p class="text-xs text-gray-400">{{ $store->phone }}</p>
            </x-filament::card>

        </div>

        {{-- ── RIGHT: Chat thread ──────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <x-filament::card class="flex flex-col" style="min-height: 600px;">

                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex-shrink-0">
                    Conversation
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $this->record->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($this->record->status) }}
                    </span>
                </h3>

                {{-- Messages --}}
                <div class="flex-1 space-y-4 overflow-y-auto mb-4" style="max-height: 400px;"
                     wire:poll.5s>
                    @forelse($this->record->messages as $msg)
                        @php $isStore = $msg->isFromStore(); @endphp
                        <div class="flex {{ $isStore ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%]">
                                <p class="text-xs text-gray-400 mb-1 {{ $isStore ? 'text-right' : 'text-left' }}">
                                    {{ $isStore ? 'You (Store Staff)' : ($this->record->retailer->business_name) }}
                                    · {{ $msg->created_at->format('M j, g:i a') }}
                                </p>
                                <div class="{{ $isStore
                                    ? 'bg-primary-600 text-white rounded-tl-2xl rounded-tr-sm rounded-bl-2xl rounded-br-2xl'
                                    : 'bg-gray-100 text-gray-800 rounded-tl-sm rounded-tr-2xl rounded-bl-2xl rounded-br-2xl'
                                }} px-4 py-2.5 text-sm">
                                    {{ $msg->body }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-8">No messages yet.</p>
                    @endforelse
                </div>

                {{-- Reply input --}}
                @if($this->record->status === 'open')
                <div class="border-t border-gray-100 pt-4 flex-shrink-0">
                    <textarea wire:model="replyBody"
                              placeholder="Type your reply…"
                              rows="3"
                              class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none mb-2"></textarea>
                    <div class="flex justify-end">
                        <x-filament::button wire:click="sendReply"
                                            wire:loading.attr="disabled">
                            Send Reply
                        </x-filament::button>
                    </div>
                </div>
                @else
                <div class="border-t border-gray-100 pt-4 text-center text-sm text-gray-400">
                    This conversation is closed.
                </div>
                @endif

            </x-filament::card>
        </div>

    </div>

</x-filament-panels::page>
