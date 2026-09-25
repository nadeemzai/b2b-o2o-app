{{-- ── Public Home: Huashu Group banner + full product listing ──────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ HERO BANNER (≥ 40 vh) ══════════════════════════════════════════ --}}
    <div class="relative w-full overflow-hidden" style="min-height: 42vh;">

        {{-- Background gradient --}}
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 35%, #0f3460 65%, #e94560 100%);"></div>

        {{-- Decorative circles --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full opacity-10" style="background: radial-gradient(circle, #ff5b00, transparent);"></div>
        <div class="absolute -bottom-16 -left-16 w-72 h-72 rounded-full opacity-10" style="background: radial-gradient(circle, #ff5b00, transparent);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-5" style="background: radial-gradient(circle, #ffffff, transparent);"></div>

        {{-- Pattern overlay --}}
        <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 24px 24px;"></div>

        {{-- Banner content --}}
        <div class="relative z-10 flex items-center" style="min-height: 42vh; padding: 3rem 2rem;">
            <div class="max-w-7xl mx-auto w-full flex flex-col lg:flex-row items-center justify-between gap-10">

                {{-- Left: branding + tagline --}}
                <div class="text-center lg:text-left">
                    {{-- Logo mark --}}
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: #ff5b00;">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-white/60 text-xs uppercase tracking-widest font-semibold">Powered by</p>
                            <p class="text-white font-bold text-lg leading-tight">Huashu Group</p>
                        </div>
                    </div>

                    <h1 class="text-white font-extrabold leading-tight mb-3"
                        style="font-size: clamp(2rem, 5vw, 3.5rem);">
                        China–Australia<br/>
                        <span style="color: #ff5b00;">B2B Wholesale</span> Platform
                    </h1>

                    <p class="text-blue-200 text-base lg:text-lg mb-6 max-w-lg">
                        Source premium Chinese products directly. Competitive factory prices, verified suppliers, seamless logistics.
                    </p>

                    <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                        <a href="{{ route('public.catalogue') }}"
                           class="inline-flex items-center gap-2 text-white font-bold px-6 py-3 rounded-xl shadow-lg hover:opacity-90 transition text-sm"
                           style="background: #ff5b00;">
                            Browse Catalogue
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('retailer.register') }}"
                           class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20 text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/20 transition text-sm">
                            Apply as Retailer
                        </a>
                    </div>
                </div>

                {{-- Right: stats / value props --}}
                <div class="grid grid-cols-2 gap-3 shrink-0">
                    @foreach([
                        ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'Premium Products', 'sub' => 'Verified quality'],
                        ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Factory Prices', 'sub' => 'No middleman'],
                        ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'label' => 'Global Shipping', 'sub' => 'Fast delivery'],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Trusted Platform', 'sub' => 'Secure trade'],
                    ] as $stat)
                    <div class="bg-white/10 backdrop-blur border border-white/15 rounded-xl p-4 text-center" style="min-width: 130px;">
                        <div class="w-9 h-9 rounded-lg bg-orange-500/30 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                            </svg>
                        </div>
                        <p class="text-white font-bold text-sm">{{ $stat['label'] }}</p>
                        <p class="text-blue-200 text-xs mt-0.5">{{ $stat['sub'] }}</p>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Bottom wave --}}
        <div class="absolute bottom-0 left-0 right-0" style="height: 40px; overflow: hidden;">
            <svg viewBox="0 0 1440 40" preserveAspectRatio="none" style="width:100%; height:100%;" fill="#f5f5f5">
                <path d="M0,40 C360,0 1080,40 1440,10 L1440,40 Z"/>
            </svg>
        </div>
    </div>

    {{-- ══ SEARCH + CATEGORY BAR ═══════════════════════════════════════════ --}}
    <div class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap gap-3 items-center justify-between">

            {{-- Search --}}
            <div class="relative flex-1" style="max-width: 420px; min-width: 200px;">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Search products..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition"/>
            </div>

            {{-- Category pills --}}
            <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-0.5">
                <a href="{{ route('public.catalogue') }}"
                   class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-600 hover:bg-orange-100 border border-orange-200 transition whitespace-nowrap">
                    All Products
                </a>
                @foreach($categories->take(8) as $cat)
                <a href="{{ route('public.catalogue', ['category' => $cat->id]) }}"
                   class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium bg-slate-50 text-slate-600 hover:bg-orange-50 hover:text-orange-600 border border-slate-200 transition whitespace-nowrap">
                    {{ $cat->name }}
                </a>
                @endforeach
                @if($categories->count() > 8)
                <a href="{{ route('public.catalogue') }}"
                   class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium bg-slate-50 text-slate-400 hover:text-brand border border-slate-200 transition whitespace-nowrap">
                    +{{ $categories->count() - 8 }} more →
                </a>
                @endif
            </div>

        </div>
    </div>

    {{-- ══ PRODUCTS GRID ═══════════════════════════════════════════════════ --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Section heading --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    @if($search)
                        Results for &ldquo;{{ $search }}&rdquo;
                    @else
                        All Products
                    @endif
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">{{ number_format($products->total()) }} products available</p>
            </div>
            <a href="{{ route('retailer.login') }}"
               class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition shadow"
               style="background:#ff5b00;">
                Login to Order
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @if($products->isEmpty())
        <div class="py-24 text-center">
            <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-slate-500 font-semibold">No products found</p>
            @if($search)
            <button wire:click="$set('search', '')" class="mt-3 text-sm text-brand underline">Clear search</button>
            @endif
        </div>
        @else

        {{-- Product cards --}}
        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
            @foreach($products as $product)
            @php $minPrice = $product->min_price; @endphp
            <a href="{{ route('public.product', $product) }}"
               class="group bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                {{-- Image --}}
                <div class="aspect-square relative overflow-hidden bg-slate-50">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}"
                             alt="{{ $product->name_en }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                    @endif

                    @if($product->is_deal ?? false)
                    <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded text-white" style="background:#ff5b00;">Deal</span>
                    @endif

                    {{-- Category badge --}}
                    @if($product->category)
                    <span class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-2">
                        <span class="text-white text-[9px] font-medium truncate block">{{ $product->category->name }}</span>
                    </span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-3 flex flex-col flex-1">
                    <p class="text-xs font-medium text-slate-700 line-clamp-2 leading-snug flex-1">
                        {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                    </p>

                    <div class="mt-2 pt-2 border-t border-slate-50 flex items-end justify-between gap-1">
                        @if($minPrice)
                        <p class="text-base font-extrabold tabular-nums" style="color:#ff5b00;">
                            {{ \App\Services\CurrencyService::format($minPrice) }}
                        </p>
                        @else
                        <p class="text-xs text-slate-400 italic">Contact for price</p>
                        @endif

                        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wide text-slate-400 border border-slate-200 px-1.5 py-0.5 rounded">
                            View
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="mt-10 flex justify-center">
            {{ $products->links() }}
        </div>
        @endif

        @endif

    </div>{{-- end products grid --}}

    {{-- ══ BOTTOM CTA STRIP ════════════════════════════════════════════════ --}}
    <div class="text-white text-center py-14 px-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
        <h3 class="text-2xl font-bold mb-2">Ready to start wholesale ordering?</h3>
        <p class="text-blue-200 text-sm mb-6">Register as a retailer and get access to wholesale pricing, bulk discounts, and dedicated support.</p>
        <a href="{{ route('retailer.register') }}"
           class="inline-flex items-center gap-2 font-bold px-8 py-3.5 rounded-xl shadow-lg hover:opacity-90 transition text-sm text-white"
           style="background:#ff5b00;">
            Apply Now — It's Free
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</div>
