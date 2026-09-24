{{-- ══════════════════════════════════════════════════════════════════════
     OZ Wholesale — Public Product Detail  (1688-style heavy data layout)
     Route: GET /product/{product}   Component: App\Livewire\Public\ProductDetail
     ══════════════════════════════════════════════════════════════════════ --}}
@php
    $locale   = app()->getLocale();
    $isZh     = $locale === 'zh_CN';

    $name     = $isZh && ($product->name_zh    ?? null) ? $product->name_zh    : $product->name_en;
    $desc     = $isZh && ($product->desc_zh    ?? null) ? $product->desc_zh    : ($product->description_en ?? '');
    $catName  = $product->category ? ($isZh && ($product->category->name_zh ?? null) ? $product->category->name_zh : $product->category->name) : null;

    $basePrice   = $product->huashu_base_price_pkr;
    $moq         = $product->moq ?? 1;
    $pcsPerCarton= $product->pieces_per_carton ?? null;
    $unit        = $product->unit ?? 'piece';

    $allImages = $product->images->count()
        ? $product->images->map(fn($img) => $img->display_url)->values()
        : ($product->image_path ? collect([asset('storage/'.$product->image_path)]) : collect());

    $variantTypes = $product->variantTypes ?? collect();
    $storePrices  = $product->storePrices  ?? collect();
@endphp

<style>
/* 1688-style product detail overrides */
.pdp-price-box   { background: linear-gradient(135deg,#fff8f5 0%,#fff3ef 100%); border:1px solid #ffe0d4; }
.pdp-tab-btn     { border-bottom: 3px solid transparent; transition: all .15s; }
.pdp-tab-btn.active { border-color: #ff5b00; color: #ff5b00; font-weight: 700; }
.pdp-attr-row td { padding: .55rem 1rem; font-size:.875rem; }
.pdp-attr-row:nth-child(even) td { background:#fafafa; }
.pdp-variant-chip {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.3rem .75rem; border-radius:.5rem;
    border:1.5px solid #e2e8f0; font-size:.8rem; font-weight:600;
    cursor:pointer; transition: all .12s;
}
.pdp-variant-chip.selected  { border-color:#ff5b00; background:#fff4f0; color:#ff5b00; }
.pdp-variant-chip:hover:not(.selected) { border-color:#ffa07a; background:#fff8f5; }
.pdp-trust-badge { display:flex; align-items:center; gap:.35rem; font-size:.78rem; color:#64748b; }
.pdp-qty-btn     { width:2rem; height:2rem; border-radius:.4rem; border:1px solid #e2e8f0;
                   display:flex; align-items:center; justify-content:center;
                   background:white; cursor:pointer; font-size:1.1rem; color:#475569;
                   transition:background .1s; }
.pdp-qty-btn:hover { background:#f1f5f9; }
.pdp-section-heading { display:flex; align-items:center; gap:.5rem; margin-bottom:1rem; font-size:1.05rem; font-weight:700; color:#1e293b; }
.pdp-section-heading::before { content:''; display:block; width:4px; height:20px; background:#ff5b00; border-radius:2px; }
/* Sticky right panel */
@media (min-width:1024px) { .pdp-right-sticky { position:sticky; top:72px; max-height:calc(100vh - 80px); overflow-y:auto; } }
</style>

<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6" x-data="{
    activeImg   : 0,
    activeTab   : 'attributes',
    qty         : {{ $moq }},
    variants    : {},
    setVariant(typeId, val) { this.variants[typeId] = val; },
    incQty()    { this.qty++; },
    decQty()    { if (this.qty > {{ $moq }}) this.qty--; },
}">

    {{-- ══ BREADCRUMB ════════════════════════════════════════════════════ --}}
    <div class="bg-slate-800 px-4 sm:px-6 lg:px-8 py-2.5">
        <nav class="flex items-center gap-2 text-xs text-slate-400 max-w-7xl mx-auto">
            <a href="{{ route('public.catalogue') }}" class="hover:text-white transition">Home</a>
            <svg class="w-3 h-3 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            @if($product->category)
            <a href="{{ route('public.catalogue') }}?category={{ $product->category_id }}" class="hover:text-white transition">{{ $catName }}</a>
            <svg class="w-3 h-3 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            @endif
            <span class="text-slate-300 truncate max-w-sm">{{ $name }}</span>
        </nav>
    </div>

    {{-- ══ MAIN PRODUCT PANEL (white background like 1688) ═══════════════ --}}
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- ── LEFT: Image Gallery ──────────────────────────────── --}}
                <div class="lg:w-[420px] shrink-0">

                    {{-- Main image --}}
                    <div class="flex gap-3">

                        {{-- Vertical thumbnail strip (hidden on mobile) --}}
                        @if($allImages->count() > 1)
                        <div class="hidden lg:flex flex-col gap-2 w-[72px] shrink-0">
                            @foreach($allImages as $i => $src)
                            <button type="button" @click="activeImg = {{ $i }}"
                                    :class="activeImg === {{ $i }} ? 'ring-2 ring-brand ring-offset-1' : 'ring-1 ring-slate-200 opacity-70 hover:opacity-100'"
                                    class="w-[72px] h-[72px] rounded-lg overflow-hidden bg-white shrink-0 transition">
                                <img src="{{ $src }}" class="w-full h-full object-cover" alt="" loading="lazy" />
                            </button>
                            @endforeach
                        </div>
                        @endif

                        {{-- Main image display --}}
                        <div class="flex-1">
                            <div class="aspect-square rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 relative flex items-center justify-center">
                                @if($allImages->count())
                                    @foreach($allImages as $i => $src)
                                    <img src="{{ $src }}"
                                         x-show="activeImg === {{ $i }}"
                                         x-cloak
                                         alt="{{ $name }}"
                                         loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                         class="w-full h-full object-contain p-2"
                                         onerror="this.parentElement.classList.add('broken')" />
                                    @endforeach
                                    {{-- Show first image always (no x-cloak on first) --}}
                                    <img src="{{ $allImages->first() }}"
                                         x-show="activeImg === 0"
                                         alt="{{ $name }}"
                                         loading="eager"
                                         class="w-full h-full object-contain p-2 absolute inset-0" />
                                @else
                                <div class="flex flex-col items-center justify-center text-slate-300">
                                    <svg class="w-20 h-20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                    <span class="text-xs font-mono">{{ $product->sku }}</span>
                                </div>
                                @endif

                                {{-- Zoom icon --}}
                                @if($allImages->count())
                                <div class="absolute bottom-2 right-2 bg-white/80 backdrop-blur-sm rounded-lg p-1.5 text-slate-400 text-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </div>
                                @endif
                            </div>

                            {{-- Mobile thumbnails --}}
                            @if($allImages->count() > 1)
                            <div class="flex gap-2 mt-3 overflow-x-auto lg:hidden pb-1">
                                @foreach($allImages as $i => $src)
                                <button type="button" @click="activeImg = {{ $i }}"
                                        :class="activeImg === {{ $i }} ? 'ring-2 ring-brand ring-offset-1' : 'ring-1 ring-slate-200 opacity-70'"
                                        class="w-14 h-14 shrink-0 rounded-lg overflow-hidden transition">
                                    <img src="{{ $src }}" class="w-full h-full object-cover" alt="" loading="lazy" />
                                </button>
                                @endforeach
                            </div>
                            @endif

                            {{-- Image counter --}}
                            @if($allImages->count() > 1)
                            <p class="text-center text-xs text-slate-400 mt-2">
                                <span x-text="activeImg + 1"></span> / {{ $allImages->count() }} photos
                            </p>
                            @endif
                        </div>
                    </div>

                    {{-- Share / Save row --}}
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100">
                        <span class="text-xs text-slate-400">Share:</span>
                        <button class="text-slate-400 hover:text-brand transition p-1.5 rounded-lg hover:bg-orange-50">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                        </button>
                        <button class="text-slate-400 hover:text-brand transition p-1.5 rounded-lg hover:bg-orange-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        </button>
                        <span class="ml-auto text-xs text-slate-400 font-mono">SKU: {{ $product->sku }}</span>
                    </div>
                </div>

                {{-- ── RIGHT: Purchase Panel ────────────────────────────── --}}
                <div class="flex-1 min-w-0 pdp-right-sticky">

                    {{-- Product name --}}
                    <h1 class="text-xl lg:text-2xl font-bold text-slate-900 leading-snug mb-1">{{ $name }}</h1>
                    @if($product->name_ur)
                    <p class="text-base text-slate-500 mb-3 font-urdu" dir="rtl" style="font-family:'Noto Nastaliq Urdu',serif;">{{ $product->name_ur }}</p>
                    @endif

                    {{-- Category + SKU tags --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($product->category)
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-orange-50 text-brand border border-orange-200 rounded-full px-3 py-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            {{ $catName }}
                        </span>
                        @endif
                        <span class="text-xs text-slate-400 font-mono bg-slate-50 rounded-full px-3 py-1 border border-slate-100">{{ $product->sku }}</span>
                        <span class="text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-3 py-1 border border-emerald-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> In Stock
                        </span>
                    </div>

                    {{-- ── PRICE BOX ───────────────────────────────────────── --}}
                    <div class="pdp-price-box rounded-2xl p-4 mb-4">
                        <div class="flex items-end gap-4 flex-wrap">
                            @if($basePrice)
                            <div>
                                <p class="text-[10px] font-semibold text-orange-400 uppercase tracking-wider mb-0.5">Wholesale Price</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-red-600 tabular-nums">PKR {{ number_format($basePrice, 0) }}</span>
                                    <span class="text-sm text-slate-500">/ {{ $unit }}</span>
                                </div>
                            </div>
                            @endif
                            @if($storePrices->isNotEmpty())
                            <div class="text-sm text-slate-500">
                                <span class="text-xs text-slate-400">Login to view store-specific discounts</span>
                            </div>
                            @endif
                        </div>

                        {{-- Price tiers / promotions --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($moq > 1)
                            <span class="inline-flex items-center gap-1 text-xs font-bold bg-red-500 text-white px-3 py-1 rounded-lg">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                                MOQ: {{ number_format($moq) }} {{ $unit }}s
                            </span>
                            @endif
                            @if($pcsPerCarton)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-orange-100 text-orange-700 px-3 py-1 rounded-lg">
                                {{ $pcsPerCarton }} pcs / carton
                            </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                7-day return
                            </span>
                        </div>

                        {{-- Store price tiers --}}
                        @if($storePrices->count() > 0)
                        <div class="mt-3 border-t border-orange-200 pt-3">
                            <p class="text-[10px] text-orange-500 font-semibold uppercase tracking-wider mb-2">Store Pricing</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach($storePrices as $sp)
                                <div class="flex items-center justify-between bg-white/60 rounded-lg px-3 py-1.5 text-xs">
                                    <span class="text-slate-500">Store #{{ $sp->store_id }}</span>
                                    <span class="font-bold text-red-600 tabular-nums">PKR {{ number_format($sp->price_pkr, 0) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1.5">
                                <a href="{{ route('retailer.login') }}" class="text-brand hover:underline">Sign in</a> to access all pricing and place orders
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- ── SUPPLY INFO ──────────────────────────────────── --}}
                    <div class="bg-slate-50 rounded-xl px-4 py-3 mb-4 space-y-2">
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-slate-500 w-24 shrink-0">Origin</span>
                            <span class="font-medium text-slate-700">China (Huashu) · Delivered via OZ Wholesale</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-slate-500 w-24 shrink-0">Lead time</span>
                            <span class="font-medium text-slate-700">Estimated 7–45 working days</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span class="text-slate-500 w-24 shrink-0">Payment</span>
                            <span class="font-medium text-slate-700">Advance Payment · 100% Secure</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                            <span class="text-slate-500 w-24 shrink-0">Unit</span>
                            <span class="font-medium text-slate-700">Per {{ $unit }}{{ $pcsPerCarton ? ' · '.$pcsPerCarton.' pcs per carton' : '' }}</span>
                        </div>
                    </div>

                    {{-- ── TRUST BADGES ─────────────────────────────────── --}}
                    <div class="flex flex-wrap gap-x-5 gap-y-2 mb-4 py-3 border-y border-slate-100">
                        <div class="pdp-trust-badge">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Quality Guaranteed
                        </div>
                        <div class="pdp-trust-badge">
                            <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Wholesale Only
                        </div>
                        <div class="pdp-trust-badge">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            7-Day Returns
                        </div>
                        <div class="pdp-trust-badge">
                            <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Dedicated Support
                        </div>
                    </div>

                    {{-- ── VARIANT SELECTORS ────────────────────────────── --}}
                    @if($variantTypes->isNotEmpty())
                    <div class="space-y-4 mb-4">
                        @foreach($variantTypes as $vt)
                        @if($vt->activeOptions->isNotEmpty())
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <p class="text-sm font-semibold text-slate-700">{{ $vt->name }}</p>
                                <span x-text="variants[{{ $vt->id }}] ?? ''" class="text-sm text-brand font-medium"></span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($vt->activeOptions as $opt)
                                <button type="button"
                                        @click="setVariant({{ $vt->id }}, '{{ $opt->value }}')"
                                        :class="variants[{{ $vt->id }}] === '{{ $opt->value }}' ? 'selected' : ''"
                                        class="pdp-variant-chip">
                                    {{ $opt->value }}
                                    @if($opt->price_adjustment_pkr && $opt->price_adjustment_pkr != 0)
                                    <span class="text-[10px] {{ $opt->price_adjustment_pkr > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                        {{ $opt->price_adjustment_pkr > 0 ? '+' : '' }}{{ number_format($opt->price_adjustment_pkr, 0) }}
                                    </span>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- ── QUANTITY ─────────────────────────────────────── --}}
                    <div class="flex items-center gap-4 mb-5">
                        <p class="text-sm font-semibold text-slate-700 w-20 shrink-0">Quantity</p>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="decQty()" class="pdp-qty-btn">−</button>
                            <input type="number" x-model="qty" min="{{ $moq }}"
                                   class="w-16 h-8 text-center text-sm font-bold border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand outline-none" />
                            <button type="button" @click="incQty()" class="pdp-qty-btn">+</button>
                        </div>
                        <span class="text-xs text-slate-400">Min. order: {{ number_format($moq) }} {{ $unit }}</span>
                    </div>

                    {{-- ── CTA BUTTONS ──────────────────────────────────── --}}
                    <div class="space-y-3">
                        <a href="{{ route('retailer.login') }}"
                           class="flex items-center justify-center gap-2 w-full py-3.5 bg-brand hover:bg-brand-dark text-white font-bold text-sm rounded-xl shadow-lg shadow-brand/25 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Sign In to Place Order
                        </a>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('retailer.register') }}"
                               class="flex items-center justify-center gap-2 py-3 border-2 border-brand text-brand font-bold text-sm rounded-xl hover:bg-orange-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Apply as Retailer
                            </a>
                            <button type="button"
                                    class="flex items-center justify-center gap-2 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                Enquire Now
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ══ TABS + DETAIL SECTIONS ══════════════════════════════════════════ --}}
    <div class="bg-white mt-2 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Tab bar --}}
            <div class="flex items-center gap-0 border-b border-slate-200 overflow-x-auto">
                @foreach([
                    ['id'=>'attributes','label'=>'Product Attributes'],
                    ['id'=>'packing',   'label'=>'Packing / Specs'],
                    ['id'=>'details',   'label'=>'Product Details'],
                    ['id'=>'reviews',   'label'=>'Reviews'],
                ] as $tab)
                <button type="button"
                        @click="activeTab = '{{ $tab['id'] }}'"
                        :class="activeTab === '{{ $tab['id'] }}' ? 'pdp-tab-btn active' : 'pdp-tab-btn text-slate-500 hover:text-slate-800'"
                        class="pdp-tab-btn px-5 py-3.5 text-sm whitespace-nowrap shrink-0">
                    {{ $tab['label'] }}
                </button>
                @endforeach
            </div>

            {{-- ── TAB: Product Attributes ──────────────────────────── --}}
            <div x-show="activeTab === 'attributes'" x-cloak class="py-6">
                <div class="pdp-section-heading">Product Attributes</div>

                @php
                    $attrs = [
                        ['label' => 'SKU / Item Code', 'value' => $product->sku],
                        ['label' => 'Category',         'value' => $catName],
                        ['label' => 'Source',            'value' => 'In stock — Ready to ship'],
                        ['label' => 'Unit',              'value' => $unit],
                        ['label' => 'MOQ',               'value' => number_format($moq).' '.$unit.'(s)'],
                        ['label' => 'Pieces / Carton',   'value' => $pcsPerCarton ? number_format($pcsPerCarton) : 'N/A'],
                        ['label' => 'Origin',            'value' => 'China (Huashu International)'],
                        ['label' => 'Delivery Time',     'value' => '7 – 45 working days'],
                        ['label' => 'Payment Terms',     'value' => '100% advance payment required'],
                        ['label' => 'Export License',    'value' => 'Yes — International Trade Enabled'],
                        ['label' => 'Return Policy',     'value' => '7-day return — Quality defects'],
                        ['label' => 'Inventory Status',  'value' => 'In Stock'],
                    ];
                    // Append variant types as attributes
                    foreach($variantTypes as $vt) {
                        if($vt->activeOptions->isNotEmpty()) {
                            $attrs[] = [
                                'label' => $vt->name,
                                'value' => $vt->activeOptions->pluck('value')->join(', '),
                            ];
                        }
                    }
                @endphp

                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full">
                        <tbody>
                            @foreach(collect($attrs)->filter(fn($a) => $a['value'])->chunk(2) as $pair)
                            <tr class="pdp-attr-row border-b border-slate-50">
                                @foreach($pair as $attr)
                                <td class="text-slate-500 font-medium bg-slate-50 w-[22%]">{{ $attr['label'] }}</td>
                                <td class="text-slate-800 w-[28%]">{{ $attr['value'] }}</td>
                                @endforeach
                                @if($pair->count() < 2)
                                <td class="bg-slate-50 w-[22%]"></td><td class="w-[28%]"></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Description --}}
                @if($desc)
                <div class="mt-6">
                    <div class="pdp-section-heading">Description</div>
                    <div class="prose prose-sm max-w-none text-slate-600 bg-slate-50 rounded-xl p-5 leading-relaxed">
                        {!! nl2br(e($desc)) !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- ── TAB: Packing / Specs ─────────────────────────────── --}}
            <div x-show="activeTab === 'packing'" x-cloak class="py-6">
                <div class="pdp-section-heading">Packing &amp; Specifications</div>

                @if($variantTypes->isNotEmpty())
                {{-- Variant matrix table --}}
                <div class="overflow-x-auto rounded-xl border border-slate-100 mb-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-brand/5 border-b border-orange-100">
                                @foreach($variantTypes as $vt)
                                <th class="text-left px-4 py-3 font-semibold text-slate-600 whitespace-nowrap">{{ $vt->name }}</th>
                                @endforeach
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">SKU Suffix</th>
                                <th class="text-right px-4 py-3 font-semibold text-slate-600">Price Adj. (PKR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($variantTypes->first()?->activeOptions ?? [] as $opt)
                            <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                                @foreach($variantTypes as $idx => $vt)
                                    @if($idx === 0)
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $opt->value }}</td>
                                    @else
                                    <td class="px-4 py-3 text-slate-500">—</td>
                                    @endif
                                @endforeach
                                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $product->sku }}{{ $opt->sku_suffix ? '-'.$opt->sku_suffix : '' }}</td>
                                <td class="px-4 py-3 text-right {{ ($opt->price_adjustment_pkr ?? 0) > 0 ? 'text-red-600 font-bold' : (($opt->price_adjustment_pkr ?? 0) < 0 ? 'text-emerald-600 font-bold' : 'text-slate-400') }}">
                                    {{ ($opt->price_adjustment_pkr ?? 0) == 0 ? 'Base price' : (($opt->price_adjustment_pkr > 0 ? '+' : '').number_format($opt->price_adjustment_pkr, 0)) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Standard packing info box --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Carton</p>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Pieces / carton</span><span class="font-bold text-slate-800">{{ $pcsPerCarton ?? 'Contact us' }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Unit of sale</span><span class="font-bold text-slate-800">{{ ucfirst($unit) }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">MOQ</span><span class="font-bold text-slate-800">{{ number_format($moq) }}</span></div>
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Shipping</p>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Origin</span><span class="font-bold text-slate-800">China</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Destination</span><span class="font-bold text-slate-800">Pakistan</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Lead time</span><span class="font-bold text-slate-800">7–45 days</span></div>
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Quality</p>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Inspection</span><span class="font-bold text-slate-800">Pre-shipment</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Returns</span><span class="font-bold text-slate-800">7 days</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Warranty</span><span class="font-bold text-slate-800">Quality defect</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── TAB: Product Details (images + desc) ─────────────── --}}
            <div x-show="activeTab === 'details'" x-cloak class="py-6">
                <div class="pdp-section-heading">Product Details</div>

                @if($allImages->count())
                <div class="space-y-4 max-w-2xl mx-auto">
                    @foreach($allImages as $src)
                    <div class="rounded-xl overflow-hidden border border-slate-100 bg-slate-50">
                        <img src="{{ $src }}" alt="{{ $name }}" loading="lazy"
                             class="w-full object-contain max-h-[600px]" />
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-16 text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm">Detailed product images coming soon</p>
                    <p class="text-xs mt-1 text-slate-300">Contact us for more information</p>
                </div>
                @endif

                @if($desc)
                <div class="mt-8 max-w-2xl mx-auto">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Description</h3>
                    <div class="text-sm text-slate-600 leading-relaxed bg-slate-50 rounded-xl p-5">
                        {!! nl2br(e($desc)) !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- ── TAB: Reviews ─────────────────────────────────────── --}}
            <div x-show="activeTab === 'reviews'" x-cloak class="py-6">
                <div class="pdp-section-heading">Product Reviews</div>

                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    {{-- Rating summary --}}
                    <div class="lg:w-48 shrink-0 text-center">
                        <div class="text-6xl font-black text-slate-800 mb-1">—</div>
                        <div class="flex justify-center gap-0.5 mb-2">
                            @for($s = 0; $s < 5; $s++)
                            <svg class="w-4 h-4 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400">No reviews yet</p>
                    </div>

                    {{-- Review prompt --}}
                    <div class="flex-1 flex flex-col items-center justify-center py-12 text-center bg-slate-50 rounded-2xl border border-slate-100">
                        <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <p class="text-sm font-semibold text-slate-500 mb-1">Be the first to review</p>
                        <p class="text-xs text-slate-400 mb-4">Sign in to leave a review after purchasing</p>
                        <a href="{{ route('retailer.login') }}" class="text-xs font-bold text-brand hover:text-brand-dark underline transition">Sign in to review</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ SUPPLIER / BRAND CARD ══════════════════════════════════════════ --}}
    <div class="bg-white mt-2 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="pdp-section-heading">Supplier Information</div>

            <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl overflow-hidden">
                <div class="flex flex-col sm:flex-row items-center sm:items-stretch gap-0">
                    {{-- Logo / brand block --}}
                    <div class="bg-brand px-8 py-8 flex items-center justify-center sm:w-56 shrink-0">
                        <div class="text-center">
                            <div class="text-white font-black text-2xl leading-none">OZ</div>
                            <div class="text-orange-200 text-xs font-medium tracking-[3px] uppercase mt-1">Wholesale</div>
                        </div>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 p-6 text-white">
                        <h3 class="text-lg font-bold mb-1">OZ Tech — B2B Wholesale Portal</h3>
                        <p class="text-slate-400 text-sm mb-4">Authorized distributor of Huashu International products in Pakistan. Verified retailer accounts only.</p>
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Response Time</p>
                                <p class="text-sm font-semibold">&lt; 24 hours</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Products</p>
                                <p class="text-sm font-semibold">{{ \App\Models\Product::where('is_active', true)->count() }}+ items</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Verified</p>
                                <p class="text-sm font-semibold text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    OZ Certified
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- CTA --}}
                    <div class="px-6 py-6 flex flex-col items-center justify-center gap-3 border-t sm:border-t-0 sm:border-l border-slate-700">
                        <a href="{{ route('retailer.login') }}" class="w-full sm:w-auto text-center text-sm font-bold bg-brand hover:bg-brand-dark text-white px-6 py-3 rounded-xl transition whitespace-nowrap">
                            Sign In to Buy
                        </a>
                        <a href="{{ route('retailer.register') }}" class="w-full sm:w-auto text-center text-sm font-semibold text-slate-400 hover:text-white px-6 py-2 transition whitespace-nowrap">
                            Apply as Retailer →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ RELATED PRODUCTS ═══════════════════════════════════════════════ --}}
    @if($related->isNotEmpty())
    <div class="bg-white mt-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="pdp-section-heading">Related Products</div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($related as $rel)
                @php
                    $relSrc   = ($rel->images->firstWhere('is_primary', true) ?? $rel->images->first())?->display_url
                                ?? ($rel->image_path ? asset('storage/'.$rel->image_path) : null);
                    $relName  = $isZh && ($rel->name_zh ?? null) ? $rel->name_zh : $rel->name_en;
                    $relPrice = $rel->min_price;
                @endphp
                <a href="{{ route('public.product', $rel) }}"
                   class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-orange-200 transition-all duration-150">
                    <div class="aspect-square bg-slate-50 relative overflow-hidden">
                        @if($relSrc)
                        <img src="{{ $relSrc }}" alt="{{ $relName }}" loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-brand/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        </div>
                        @endif
                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-brand text-xs font-bold bg-white/90 px-3 py-1 rounded-full shadow">View Details</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2 mb-1.5">{{ $relName }}</h3>
                        <div class="flex items-center justify-between">
                            @if($relPrice)
                            <p class="text-sm font-black text-red-600 tabular-nums">PKR {{ number_format($relPrice, 0) }}</p>
                            @else
                            <p class="text-xs text-slate-400 italic">Contact</p>
                            @endif
                            <svg class="w-4 h-4 text-brand opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
