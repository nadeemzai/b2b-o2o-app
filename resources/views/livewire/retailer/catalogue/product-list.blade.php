{{-- ── 1688-style B2B Catalogue ──────────────────────────────────────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

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

                {{-- ══ PRODUCT GRID — Homepage-style compact cards ══ --}}
                <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));">
                    @foreach($products as $product)
                    @php
                        $basePrice  = (float) ($product->huashu_base_price_pkr ?? 0);
                        $rate       = $commissionRates[$product->category_id] ?? 0.0;
                        $price      = $basePrice > 0 ? round($basePrice * (1 + $rate), 2) : 0;
                        $moq        = max(1, (int) ($product->moq ?? 1));
                        $stock      = $product->stockLevels->first();
                        $inStock    = $stock === null || $stock->qty_available > 0;
                        $qty        = $stock?->qty_available ?? null;
                        $tracked    = $stock !== null;
                        $primaryImg = $product->primaryImage();
                        $displaySrc = $primaryImg
                            ? $primaryImg->display_url
                            : ($product->image_path ? asset('storage/' . $product->image_path) : null);
                        // Rating & sold — columns may not exist yet in older deployments
                        $rating    = isset($product->rating)     ? (float) $product->rating     : null;
                        $soldCount = isset($product->sold_count) ? (int)   $product->sold_count : null;
                        $ratingInt = $rating !== null ? (int) round($rating) : 0;
                        $soldLabel = $soldCount !== null
                            ? ($soldCount >= 1000 ? round($soldCount / 1000, 1) . 'k' : $soldCount)
                            : null;
                    @endphp

                    <a href="{{ route('retailer.catalogue.product', $product) }}"
                       class="group bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                        {{-- ── Image ────────────────────────────── --}}
                        <div class="aspect-square relative overflow-hidden bg-slate-50">
                            @if($displaySrc)
                                <img src="{{ $displaySrc }}"
                                     alt="{{ $product->name_en }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center"
                                     style="background: linear-gradient(135deg, #fff5f0 0%, #fff 100%);">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-brand">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif

                            {{-- Out-of-stock dim --}}
                            @if(!$inStock)
                            <div class="absolute inset-0 bg-white/65 flex items-center justify-center">
                                <span class="bg-slate-700/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full tracking-wide uppercase">{{ __('ui.out_of_stock') }}</span>
                            </div>
                            @endif

                            {{-- Category gradient at bottom of image --}}
                            @if($product->category)
                            <span class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/55 to-transparent px-2 py-1.5 pointer-events-none">
                                <span class="text-white text-[9px] truncate block leading-tight">
                                    {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                                </span>
                            </span>
                            @endif
                        </div>

                        {{-- ── Info ─────────────────────────────── --}}
                        <div class="p-2.5 flex flex-col flex-1">

                            {{-- Product name --}}
                            <p class="text-xs font-medium text-slate-700 line-clamp-2 leading-snug flex-1 mb-1">
                                {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                            </p>

                            {{-- Rating stars + sold count --}}
                            @if($rating !== null || $soldLabel !== null)
                            <div class="flex items-center gap-1 mb-1.5">
                                @if($rating !== null)
                                <span class="flex items-center gap-0.5" aria-label="{{ number_format($rating, 1) }} stars">
                                    @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $ratingInt)
                                    <svg class="w-2.5 h-2.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @else
                                    <svg class="w-2.5 h-2.5 text-slate-200" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @endif
                                    @endfor
                                    <span class="text-[9px] text-slate-400 ml-0.5">{{ number_format($rating, 1) }}</span>
                                </span>
                                @endif
                                @if($soldLabel !== null)
                                <span class="text-[9px] text-slate-400">· {{ $soldLabel }} sold</span>
                                @endif
                            </div>
                            @endif

                            {{-- Price + add-to-cart icon --}}
                            <div class="mt-auto pt-1.5 border-t border-slate-50 flex items-center justify-between gap-1">
                                <div class="min-w-0">
                                    @if($price > 0)
                                    <x-price :value="$price" class="text-sm font-extrabold text-brand" />
                                    @if($moq > 1)
                                    <p class="text-[9px] text-orange-400 font-medium mt-0.5">Min {{ $moq }} {{ $product->unit }}</p>
                                    @endif
                                    @else
                                    <p class="text-[10px] text-slate-400 italic">{{ __('ui.contact_for_price') }}</p>
                                    @endif
                                </div>

                                {{-- Round add-to-cart icon button --}}
                                @if($inStock && $price > 0)
                                <button
                                    wire:click.prevent="addToCart({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="addToCart({{ $product->id }})"
                                    class="shrink-0 w-7 h-7 rounded-lg bg-brand text-white flex items-center justify-center hover:bg-orange-600 active:scale-95 transition shadow-sm"
                                    title="{{ __('ui.add_to_cart') }}"
                                    @click.stop>
                                    <span wire:loading.remove wire:target="addToCart({{ $product->id }})">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="addToCart({{ $product->id }})">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </span>
                                </button>
                                @endif
                            </div>

                            {{-- Stock badge (when enabled) --}}
                            @if($showStockBadge)
                            <div class="flex items-center gap-1 mt-1.5">
                                @if($inStock && $tracked)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-[9px] text-emerald-600 font-medium">{{ number_format($qty) }} {{ __('ui.in_stock') }}</span>
                                @elseif($inStock && !$tracked)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                    <span class="text-[9px] text-emerald-600 font-medium">{{ __('ui.in_stock') }}</span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                    <span class="text-[9px] text-slate-400">{{ __('ui.out_of_stock') }}</span>
                                @endif
                            </div>
                            @endif

                        </div>
                    </a>
                    @endforeach
                </div>
                </div>

                {{-- ── Infinite scroll sentinel ─────────────────────────────── --}}
                @if($hasMore)
                <div class="h-2 mt-6"
                     x-data="{
                         init() {
                             let busy = false;
                             const io = new IntersectionObserver(([entry]) => {
                                 if (entry.isIntersecting && !busy) {
                                     busy = true;
                                     $wire.loadMore().then(() => { busy = false; });
                                 }
                             }, { rootMargin: '300px' });
                             io.observe(this.$el);
                         }
                     }">
                </div>
                <div wire:loading.flex class="justify-center py-4">
                    <span class="text-sm text-slate-400 animate-pulse">Loading more products…</span>
                </div>
                @else
                <p class="text-center py-8 text-slate-300 text-sm font-medium tracking-wide">— No more products —</p>
                @endif
                @endif
            </div>

        </div>{{-- /product area --}}
    </div>{{-- /main content --}}

</div>
