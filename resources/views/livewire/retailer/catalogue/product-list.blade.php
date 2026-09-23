{{-- ── 1688-style B2B Catalogue ──────────────────────────────────────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">



    {{-- ══ FILTER BAR ═════════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-2.5">
        <div class="flex flex-wrap items-center gap-3 max-w-screen-xl mx-auto">

            {{-- Inline keyword filter --}}
            <div class="flex items-center border border-slate-300 rounded overflow-hidden h-8 flex-1 min-w-[180px] max-w-xs">
                <input type="text"
                       wire:model.live.debounce.350ms="search"
                       placeholder="{{ __('ui.search_placeholder') }}"
                       class="flex-1 h-full px-3 text-sm text-slate-700 placeholder-slate-400 focus:outline-none" />
                <span class="px-2.5 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
            </div>

            {{-- Category filter --}}
            <select wire:model.live="categoryId"
                    class="h-8 border border-slate-300 rounded text-sm text-slate-700 pl-3 pr-7 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand min-w-[150px]">
                <option value="">{{ __('ui.filter_category') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}</option>
                @endforeach
            </select>

            {{-- Sort --}}
            <select wire:model.live="sortBy"
                    class="h-8 border border-slate-300 rounded text-sm text-slate-700 pl-3 pr-7 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand min-w-[140px]">
                <option value="name_asc">{{ __('ui.sort_name_az') }}</option>
                <option value="name_desc">{{ __('ui.sort_name_desc') }}</option>
                <option value="price_asc">{{ __('ui.sort_price_asc') }}</option>
                <option value="price_desc">{{ __('ui.sort_price_desc') }}</option>
            </select>

            {{-- Result count --}}
            <span class="ml-auto text-xs text-slate-400 whitespace-nowrap hidden sm:inline">
                {{ $products->total() }} {{ __('ui.products_found') }}
            </span>
        </div>
    </div>

    {{-- ══ ADDED TO CART FLASH ═════════════════════════════════════════════ --}}
    @if(session('cart_added'))
    <div class="mx-4 sm:mx-6 lg:mx-8 mt-3"
         x-data x-init="setTimeout(() => $el.remove(), 3000)">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-2.5 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <strong>{{ session('cart_added') }}</strong>&nbsp;added to cart.
        </div>
    </div>
    @endif

    {{-- ══ MAIN CONTENT: SIDEBAR + PRODUCTS ═══════════════════════════════ --}}
    <div class="flex gap-0 px-4 sm:px-6 lg:px-8 mt-4 pb-10 items-start">

        {{-- ── LEFT SIDEBAR ────────────────────────────────────────────── --}}
        <aside class="w-52 shrink-0 mr-5 sticky top-20 self-start hidden md:block">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-brand px-4 py-3">
                    <h2 class="text-white font-semibold text-sm tracking-wide uppercase">{{ __('ui.categories') }}</h2>
                </div>

                <nav class="divide-y divide-slate-50">
                    <button wire:click="$set('categoryId', '')"
                            class="w-full text-left px-4 py-3 text-sm transition flex items-center justify-between
                                   {{ $categoryId === '' ? 'bg-orange-50 text-brand font-semibold border-l-4 border-brand' : 'text-slate-700 hover:bg-slate-50 border-l-4 border-transparent' }}">
                        <span>{{ __('ui.all_products') }}</span>
                        <span class="text-xs font-normal {{ $categoryId === '' ? 'text-brand' : 'text-slate-400' }}">
                            {{ $totalProducts }}
                        </span>
                    </button>

                    @foreach($categories as $cat)
                    <button wire:click="$set('categoryId', '{{ $cat->id }}')"
                            class="w-full text-left px-4 py-3 text-sm transition flex items-center gap-2
                                   {{ $categoryId == $cat->id ? 'bg-orange-50 text-brand font-semibold border-l-4 border-brand' : 'text-slate-700 hover:bg-slate-50 border-l-4 border-transparent' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 {{ $categoryId == $cat->id ? 'text-brand' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                        <span class="truncate">{{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}</span>
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Account quick-links --}}
            <div class="mt-4 bg-brand rounded-xl p-4 text-white text-center">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-200 mb-1">{{ __('ui.my_account') }}</p>
                <p class="text-sm font-medium mb-3">{{ auth()->user()->name }}</p>
                <a href="{{ route('retailer.orders') }}"
                   class="block bg-white text-brand hover:bg-orange-50 text-sm font-bold px-4 py-2 rounded-lg transition shadow">
                    {{ __('ui.my_orders') }}
                </a>
            </div>
        </aside>

        {{-- ── PRODUCT AREA ─────────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Sort / results toolbar --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-4 py-3 mb-4 flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>
                        <strong class="text-slate-800 tabular-nums">{{ $totalProducts }}</strong> {{ __('ui.products_count') }}
                        @if($search) &nbsp;for "<em>{{ $search }}</em>" @endif
                        @if($categoryId && $categories->firstWhere('id', $categoryId)) in <strong>{{ app()->getLocale() === 'zh_CN' && $categories->firstWhere('id', $categoryId)?->name_zh ? $categories->firstWhere('id', $categoryId)?->name_zh : $categories->firstWhere('id', $categoryId)?->name }}</strong> @endif
                    </span>
                </div>

                {{-- Sort tabs --}}
                <div class="flex items-center gap-1 text-sm">
                    <span class="text-slate-400 mr-1 text-xs">Sort:</span>
                    @foreach([
                        'name_asc'   => __('ui.sort_name_az'),
                        'price_asc'  => __('ui.sort_price_asc'),
                        'price_desc' => __('ui.sort_price_desc'),
                        'newest'     => __('ui.sort_newest'),
                    ] as $val => $label)
                    <button wire:click="$set('sortBy', '{{ $val }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                                   {{ $sortBy === $val ? 'bg-brand text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Loading overlay --}}
            <div wire:loading class="text-center py-4 text-sm text-slate-400 flex items-center justify-center gap-2">
                <svg class="animate-spin w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                {{ __('ui.loading') }}
            </div>

            {{-- Products grid --}}
            <div wire:loading.class="opacity-40 pointer-events-none">
                @if($products->isEmpty())
                <div class="bg-white rounded-xl border border-slate-100 py-20 text-center">
                    <svg class="w-14 h-14 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                    <p class="text-slate-500 font-medium">{{ __('ui.no_products') }}</p>
                    <p class="text-slate-400 text-sm mt-1">{{ __('ui.no_products_hint') }}</p>
                    @if($search || $categoryId)
                    <button wire:click="$set('search', ''); $set('categoryId', '')"
                            class="mt-4 text-brand text-sm hover:underline">{{ __('ui.clear_filters') }}</button>
                    @endif
                </div>
                @else

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-5 gap-3">
                    @foreach($products as $product)
                    @php
                        $basePrice  = (float) ($product->huashu_base_price_pkr ?? 0);
                        $rate       = $commissionRates[$product->category_id] ?? 0.0;
                        $price      = $basePrice > 0 ? round($basePrice * (1 + $rate), 2) : 0;
                        $moq        = max(1, (int) ($product->moq ?? 1));
                        $stock      = $product->stockLevels->first();
                        // No stock_levels row = stock not tracked → treat as available
                        // A row exists but qty_available = 0 → truly out of stock
                        $inStock    = $stock === null || $stock->qty_available > 0;
                        $qty        = $stock?->qty_available ?? null; // null = untracked
                        $tracked    = $stock !== null;
                    @endphp

                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-orange-100 transition-all duration-150">

                        {{-- Product image (clickable → detail page) --}}
                        <a href="{{ route('retailer.catalogue.product', $product) }}"
                           class="aspect-square bg-gradient-to-br from-slate-50 to-slate-100 relative overflow-hidden block">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                     alt="{{ $product->name_en }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                            @else
                                {{-- Branded placeholder --}}
                                <div class="w-full h-full flex flex-col items-center justify-center p-4">
                                    <div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center mb-2">
                                        <svg class="w-7 h-7 text-brand/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                    <p class="text-[10px] text-slate-300 font-mono tracking-wider">{{ $product->sku }}</p>
                                </div>
                            @endif

                            {{-- Out of stock overlay --}}
                            @if(!$inStock)
                            <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
                                <span class="bg-slate-700 text-white text-[11px] font-bold px-3 py-1 rounded-full tracking-wide uppercase">{{ __('ui.out_of_stock') }}</span>
                            </div>
                            @endif

                            {{-- Category badge --}}
                            @if($product->category)
                            <div class="absolute top-2 left-2">
                                <span class="bg-white/90 backdrop-blur-sm text-slate-500 text-[10px] font-medium px-2 py-0.5 rounded-full border border-slate-100 shadow-sm">
                                    {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                                </span>
                            </div>
                            @endif
                        </a>

                        {{-- Product info --}}
                        <div class="p-3 flex flex-col flex-1">

                            {{-- Name (clickable → detail page) --}}
                            <a href="{{ route('retailer.catalogue.product', $product) }}" class="hover:text-brand transition">
                                <h3 class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2 mb-1 min-h-[2.5rem]">
                                    {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                </h3>
                            </a>

                            {{-- Price - large & prominent like 1688 --}}
                            <div class="mt-1 mb-2">
                                @if($price > 0)
                                <p class="text-xl font-bold text-brand tabular-nums leading-none">
                                    {{ \App\Services\CurrencyService::format($price) }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">per {{ $product->unit }}
                                    @if($product->pieces_per_carton) · {{ $product->pieces_per_carton }} pcs/ctn @endif
                                </p>
                                @if($moq > 1)
                                <p class="text-[11px] text-orange-500 mt-0.5 font-medium">Min. {{ $moq }} {{ $product->unit }}</p>
                                @endif
                                @else
                                <p class="text-sm text-slate-400 italic">{{ __('ui.contact_for_price') }}</p>
                                @endif
                            </div>

                            {{-- Stock info --}}
                            <div class="flex items-center gap-1.5 mb-3">
                                @if($inStock && $tracked)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-[11px] text-emerald-600 font-medium">{{ number_format($qty) }} {{ __('ui.in_stock') }}</span>
                                @elseif($inStock && !$tracked)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                    <span class="text-[11px] text-emerald-600 font-medium">{{ __('ui.in_stock') }}</span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                    <span class="text-[11px] text-slate-400">{{ __('ui.out_of_stock') }}</span>
                                @endif
                            </div>

                            {{-- Add to cart --}}
                            <div class="mt-auto">
                                <button
                                    wire:click="addToCart({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="addToCart({{ $product->id }})"
                                    @if(!$inStock || $price == 0) disabled @endif
                                    class="w-full py-2 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1.5
                                           {{ $inStock && $price > 0
                                               ? 'bg-brand text-white hover:bg-brand-dark active:scale-95 shadow-sm'
                                               : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">

                                    <span wire:loading.remove wire:target="addToCart({{ $product->id }})">
                                        @if($inStock && $price > 0)
                                        <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        {{ $moq > 1 ? 'Add ' . $moq . ' ' . $product->unit : __('ui.add_to_cart') }}
                                        @else
                                        {{ __('ui.unavailable') }}
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="addToCart({{ $product->id }})" class="flex items-center gap-1">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        {{ __('ui.adding') }}
                                    </span>
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

        </div>{{-- /product area --}}
    </div>{{-- /main content --}}

</div>
