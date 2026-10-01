<div class="min-h-screen bg-slate-50 dark:bg-slate-900 pt-4">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ── Page header ────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Messages</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Conversations with suppliers about products
            </p>
        </div>

        {{-- ── Empty state ─────────────────────────────────────────────────────── --}}
        @if($conversations->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-orange-50 dark:bg-orange-950/40 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-brand/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300">No conversations yet</h3>
                <p class="mt-1 text-sm text-slate-400 max-w-xs">
                    Open any product and tap <strong>Ask Supplier</strong> to start a conversation.
                </p>
                <a href="{{ route('retailer.catalogue') }}"
                   class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition shadow">
                    Browse Catalogue
                </a>
            </div>

        {{-- ── Conversation list ───────────────────────────────────────────────── --}}
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-800/60 rounded-2xl shadow-sm overflow-hidden">
                @foreach($conversations as $conv)
                    @php
                        $product     = $conv->product;
                        $lastMsg     = $conv->messages->first();
                        $unread      = $conv->unreadByRetailer();
                        $image       = $product?->primaryImage()?->url
                                        ?? $product?->images->first()?->url
                                        ?? null;
                        $productName = $product?->name_en ?? 'Product';
                        $productUrl  = $product
                                        ? route('retailer.catalogue.product', $product) . '?chat=1'
                                        : '#';
                    @endphp

                    <a href="{{ $productUrl }}"
                       class="flex items-center gap-4 px-5 py-4
                              hover:bg-orange-50/50 dark:hover:bg-slate-700/50
                              transition group relative">

                        {{-- Unread stripe --}}
                        @if($unread > 0)
                            <span class="absolute left-0 top-0 bottom-0 w-0.5 bg-brand rounded-r"></span>
                        @endif

                        {{-- Product thumbnail --}}
                        <div class="shrink-0 w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $productName }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate
                                          {{ $unread > 0 ? 'font-bold' : '' }}">
                                    {{ $productName }}
                                </p>
                                @if($lastMsg)
                                    <span class="shrink-0 text-[11px] text-slate-400 dark:text-slate-500">
                                        {{ $lastMsg->created_at->diffForHumans(short: true) }}
                                    </span>
                                @endif
                            </div>

                            @if($lastMsg)
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400 truncate
                                          {{ $unread > 0 ? 'font-medium text-slate-700 dark:text-slate-300' : '' }}">
                                    {{ $lastMsg->isFromRetailer() ? 'You: ' : 'Supplier: ' }}{{ Str::limit($lastMsg->body, 60) }}
                                </p>
                            @else
                                <p class="mt-0.5 text-sm text-slate-400 italic">No messages yet</p>
                            @endif
                        </div>

                        {{-- Unread badge --}}
                        <div class="shrink-0 flex items-center gap-2">
                            @if($unread > 0)
                                <span class="min-w-[20px] h-5 px-1 bg-brand text-white text-[10px] font-bold
                                             rounded-full flex items-center justify-center">
                                    {{ $unread > 99 ? '99+' : $unread }}
                                </span>
                            @endif
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
