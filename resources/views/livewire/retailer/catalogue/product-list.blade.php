<div class="space-y-5">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-800">Product Catalogue</h1>
        <a href="{{ route('retailer.cart') }}" class="flex items-center gap-2 text-sm text-brand hover:underline font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Go to Cart
        </a>
    </div>

    {{-- Added to cart flash --}}
    @if(session('cart_added'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-2.5 text-sm"
         x-data x-init="setTimeout(() => $el.remove(), 3000)">
        ✓ <strong>{{ session('cart_added') }}</strong> added to cart.
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search products or SKU…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" />
        </div>
        <select wire:model.live="categoryId" class="sm:w-52 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Product grid --}}
    <div wire:loading.class="opacity-60">
        @if($products->isEmpty())
        <div class="bg-white rounded-xl border border-slate-100 py-16 text-center text-slate-400 text-sm">
            No products found for your store.
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($products as $product)
            @php
                $price   = $product->storePrices->first()?->price_pkr ?? 0;
                $stock   = $product->stockLevels->first();
                $inStock = $stock && $stock->qty_available > 0;
            @endphp
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">

                {{-- Product image placeholder --}}
                <div class="h-36 bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name_en }}" class="h-full w-full object-cover" />
                    @else
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                        </svg>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <p class="text-xs text-slate-400 font-mono mb-1">{{ $product->sku }}</p>
                    <h3 class="text-sm font-semibold text-slate-800 leading-snug mb-1">{{ $product->name_en }}</h3>
                    <p class="text-xs text-slate-400 mb-3">
                        {{ $product->category?->name }} &middot; {{ $product->pieces_per_carton }} pcs/carton
                    </p>

                    <div class="mt-auto">
                        <div class="flex items-end justify-between mb-3">
                            <div>
                                <p class="text-lg font-bold text-slate-900 tabular-nums">
                                    PKR {{ number_format($price, 0) }}
                                </p>
                                <p class="text-xs text-slate-400">per {{ $product->unit }}</p>
                            </div>
                            @if($inStock)
                            <span class="text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-0.5 rounded-full">
                                {{ $stock->qty_available }} in stock
                            </span>
                            @else
                            <span class="text-xs text-red-500 font-medium bg-red-50 px-2 py-0.5 rounded-full">Out of stock</span>
                            @endif
                        </div>

                        <button
                            wire:click="addToCart({{ $product->id }})"
                            wire:loading.attr="disabled"
                            wire:target="addToCart({{ $product->id }})"
                            @if(!$inStock || $price == 0) disabled @endif
                            class="w-full py-2 rounded-lg text-sm font-semibold transition
                                   {{ $inStock && $price > 0
                                       ? 'bg-brand text-white hover:bg-blue-800'
                                       : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                            <span wire:loading.remove wire:target="addToCart({{ $product->id }})">Add to Cart</span>
                            <span wire:loading wire:target="addToCart({{ $product->id }})">Adding…</span>
                        </button>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
