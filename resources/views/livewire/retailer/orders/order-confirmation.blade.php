<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-2xl mx-auto px-4">

        {{-- ── Success Banner ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-10 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-1">Order Placed!</h1>
                <p class="text-green-100 text-sm">Your order has been received and is pending payment verification.</p>
            </div>

            {{-- ── Order Meta ──────────────────────────────────────── --}}
            <div class="px-8 py-6">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center">
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Order #</div>
                        <div class="text-lg font-bold text-gray-800">#{{ $order->id }}</div>
                    </div>
                    <div class="text-center border-x border-gray-100">
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total (PKR)</div>
                        <div class="text-lg font-bold text-gray-800">{{ number_format($order->total_pkr, 0) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Status</div>
                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 mt-1">
                            Pending
                        </div>
                    </div>
                </div>

                {{-- Store info --}}
                @if($order->store)
                <div class="bg-blue-50 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Fulfilling Store</p>
                        <p class="text-xs text-blue-700">{{ $order->store->name }}</p>
                    </div>
                </div>
                @endif

                {{-- ── Next Step Alert ─────────────────────────────── --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-900">Action Required</p>
                        <p class="text-xs text-amber-700 mt-0.5">Please upload your payment proof so our team can verify and process your order.</p>
                    </div>
                </div>

                {{-- ── Items Table ──────────────────────────────────── --}}
                <div class="border border-gray-100 rounded-xl overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Order Items ({{ $order->items->count() }})</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                        <div class="px-4 py-3 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $item->product?->name_en ?? 'Product #'.$item->product_id }}</p>
                                <p class="text-xs text-gray-400">{{ $item->qty }} {{ $item->unit }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-800">PKR {{ number_format($item->line_total_pkr, 0) }}</p>
                                <p class="text-xs text-gray-400">@ PKR {{ number_format($item->unit_price_pkr, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="text-base font-bold text-gray-900">PKR {{ number_format($order->total_pkr, 0) }}</span>
                    </div>
                </div>

                {{-- ── CTA Buttons ─────────────────────────────────── --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('retailer.orders') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        View Orders & Upload Proof
                    </a>
                    <a href="{{ route('retailer.catalogue') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Confirmation Reference ───────────────────────────────── --}}
        <p class="text-center text-xs text-gray-400">
            A confirmation email has been sent to your registered email address. &nbsp;·&nbsp;
            Placed {{ $order->created_at->format('d M Y, H:i') }}
        </p>

    </div>
</div>
