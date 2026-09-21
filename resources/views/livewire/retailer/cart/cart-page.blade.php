<div class="max-w-3xl mx-auto space-y-5">

    <h1 class="text-xl font-bold text-slate-800">Shopping Cart</h1>

    @if(empty($cartProducts))
    <div class="bg-white rounded-xl border border-slate-100 py-20 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-slate-500 text-sm mb-4">Your cart is empty.</p>
        <a href="{{ route('retailer.catalogue') }}" class="bg-brand text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-800 transition">
            Browse Catalogue
        </a>
    </div>
    @else

    {{-- Cart items --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 text-sm font-semibold text-slate-600">
            {{ count($cartProducts) }} item(s)
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($cartProducts as $productId => $item)
            <div class="px-5 py-4 flex items-center gap-4">
                {{-- Product info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $item['name'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $item['unit'] }} &middot; PKR {{ number_format($item['price'], 0) }} each</p>
                </div>

                {{-- Qty controls --}}
                <div class="flex items-center gap-2">
                    <button wire:click="updateQty({{ $productId }}, {{ max(0, $item['qty'] - 1) }})"
                            class="w-7 h-7 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition text-sm font-bold">−</button>
                    <span class="w-8 text-center text-sm font-semibold text-slate-800 tabular-nums">{{ $item['qty'] }}</span>
                    <button wire:click="updateQty({{ $productId }}, {{ $item['qty'] + 1 }})"
                            class="w-7 h-7 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition text-sm font-bold">+</button>
                </div>

                {{-- Line total --}}
                <div class="text-right w-24">
                    <p class="text-sm font-bold text-slate-800 tabular-nums">PKR {{ number_format($item['price'] * $item['qty'], 0) }}</p>
                </div>

                {{-- Remove --}}
                <button wire:click="remove({{ $productId }})" class="text-slate-300 hover:text-red-400 transition ml-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Order notes + summary --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Order Notes <span class="text-slate-400">(optional)</span></label>
            <textarea wire:model="notes" rows="2" placeholder="Any special instructions for the store…"
                      class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"></textarea>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm text-slate-500">Subtotal</span>
                <span class="text-sm font-semibold text-slate-800 tabular-nums">PKR {{ number_format($total, 0) }}</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-slate-500">Payment</span>
                <span class="text-sm text-slate-600 bg-slate-100 px-2 py-0.5 rounded">Cash on Delivery</span>
            </div>
            <div class="flex items-center justify-between text-base font-bold text-slate-900 border-t border-slate-100 pt-3">
                <span>Total</span>
                <span class="tabular-nums">PKR {{ number_format($total, 0) }}</span>
            </div>
        </div>

        <button wire:click="placeOrder" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                class="w-full bg-brand hover:bg-blue-800 text-white font-bold py-3 rounded-lg transition text-sm">
            <span wire:loading.remove>Place Order — PKR {{ number_format($total, 0) }}</span>
            <span wire:loading>Placing order…</span>
        </button>

        <p class="text-xs text-center text-slate-400">
            COD only &middot; Your store will contact you to confirm delivery time
        </p>
    </div>

    @endif

</div>
