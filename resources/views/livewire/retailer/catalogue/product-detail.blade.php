{{-- ── Retailer Product Detail — 1688-style with live Add to Cart ─────────── --}}


@php
$locale    = app()->getLocale();
$isZh      = $locale === 'zh_CN';
$name      = $isZh && ($product->name_zh ?? null) ? $product->name_zh : $product->name_en;
$catName   = $product->category ? ($isZh && ($product->category->name_zh ?? null) ? $product->category->name_zh : $product->category->name) : null;
$moq       = max(1, (int) $product->moq);
$unit      = $product->unit ?? 'piece';
$pcsCarton = $product->pieces_per_carton ?? null;
$allImages = $product->images->count()
    ? $product->images->map(fn($img) => $img->display_url)->values()
    : ($product->image_path ? collect([asset('storage/'.$product->image_path)]) : collect());
$inStock   = ! $stockTracked || $available > 0;
$maxQty    = $stockTracked ? $available : 9999;
$rating    = isset($product->rating) && $product->rating !== null ? (float) $product->rating : null;
$soldCount = isset($product->sold_count) ? (int) $product->sold_count : 0;
$ratingInt = $rating !== null ? (int) round($rating) : 0;
$soldLabel = $soldCount > 0
    ? ($soldCount >= 1000 ? round($soldCount / 1000, 1).'k' : $soldCount)
    : null;
@endphp

<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">
    <style>
.rpdp-price-box   { background: linear-gradient(135deg,#fff8f5 0%,#fff3ef 100%); border:1px solid #ffe0d4; }
.rpdp-tab-btn     { border-bottom: 3px solid transparent; transition: all .15s; cursor: pointer; white-space: nowrap; }
.rpdp-tab-btn.active { border-color: #ff5b00; color: #ff5b00; font-weight: 700; }
.rpdp-attr-row td { padding: .55rem 1rem; font-size:.875rem; }
.rpdp-attr-row:nth-child(even) td { background:#fafafa; }
.rpdp-variant-card { border:1.5px solid #e5e7eb; border-radius:12px; transition:all .15s; background:#fff; }
.rpdp-variant-card.selected { border-color:#ff5b00; background:#fff4f0; }
.rpdp-right-sticky { position:sticky; top:72px; max-height:calc(100vh - 80px); overflow-y:auto; }
.rpdp-thumb { border:2px solid transparent; border-radius:8px; overflow:hidden; cursor:pointer; transition:all .15s; }
.rpdp-thumb.active { border-color:#ff5b00; }
.rpdp-thumb:hover  { border-color:#ffb899; }
@media (min-width:1024px) { .rpdp-gallery-wrap { display:flex; flex-direction:row; gap:10px; } }
@media (max-width:1023px) { .rpdp-thumb-strip { display:none; } }
    </style>

    <script>
    function productPricing(tiers, moq, basePrice, maxQty, initialQty) {
        return {
            qty: initialQty ?? 1,
            tiers: tiers,
            moq: moq,
            basePrice: basePrice,
            maxQty: maxQty,

            get unitPrice() {
                if (!this.tiers.length) return this.basePrice;
                let q = parseInt(this.qty) || 1;
                for (let i = this.tiers.length - 1; i >= 0; i--) {
                    if (q >= this.tiers[i].min_qty) return this.tiers[i].price_pkr;
                }
                return this.basePrice; // below all tiers: show base retailer price
            },

            get activeTierIdx() {
                if (!this.tiers.length) return -1;
                let q = parseInt(this.qty) || 1;
                for (let i = this.tiers.length - 1; i >= 0; i--) {
                    if (q >= this.tiers[i].min_qty) return i;
                }
                return -1; // below all tiers: no tier highlighted
            },

            get upsellMsg() {
                if (!this.tiers.length) return '';
                let q = parseInt(this.qty) || 1;
                let next = this.tiers.find(t => t.min_qty > q);
                if (!next) return '';
                let gap = next.min_qty - q;
                return 'Add ' + gap + ' more to unlock PKR ' + Number(next.price_pkr).toFixed(2) + '/unit';
            },

            fmt(n) {
                return 'PKR ' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(n);
            },

            init() {
                // Entangle with Livewire
                let wire = this.$wire;
                Object.defineProperty(this, 'qty', {
                    get: () => wire.qty,
                    set: (v) => { wire.qty = v; },
                    configurable: true,
                    enumerable: true,
                });
            }
        };
    }
    </script>


    {{-- ══ BREADCRUMB BAR ═══════════════════════════════════════════════ --}}
    <div class="bg-brand px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <nav class="flex items-center gap-2 text-sm text-white/80">
                <a href="{{ route('retailer.catalogue') }}" class="hover:text-white transition">Catalogue</a>
                <svg class="w-3 h-3 text-white/40 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                @if($catName)
                <span class="text-white/60">{{ $catName }}</span>
                <svg class="w-3 h-3 text-white/40 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                @endif
                <span class="text-white truncate max-w-xs">{{ $name }}</span>
            </nav>
            <a href="{{ route('retailer.cart') }}"
               class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition border border-white/20 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                View Cart
            </a>
        </div>
    </div>

    {{-- ══ CART ADDED FLASH ══════════════════════════════════════════════ --}}
    @if(session('cart_added'))
    <div class="mx-4 sm:mx-6 lg:mx-8 mt-3" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span><strong>{{ session('cart_added') }}</strong> added to your cart.</span>
            <a href="{{ route('retailer.cart') }}" class="ml-auto text-emerald-700 font-semibold hover:underline text-xs">View Cart →</a>
        </div>
    </div>
    @endif

    {{-- ══ MAIN CONTENT ═════════════════════════════════════════════════ --}}
    <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto"
         x-data="{ activeImg: 0, activeTab: 'attributes' }">

        {{-- ── 1688-STYLE TWO-COLUMN LAYOUT ─────────────────────────── --}}
        <div class="flex flex-col xl:flex-row gap-5 items-start">

            {{-- ══ LEFT COLUMN: Gallery only ══════════════════════ --}}
            <div class="w-full xl:w-1/2 min-w-0">

                {{-- ── Gallery Card ─────────────────────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-visible">
                    <div class="p-4">
                        <div class="rpdp-gallery-wrap">

                            {{-- Vertical thumbnail strip (desktop only) --}}
                            <div class="rpdp-thumb-strip flex flex-col gap-2 shrink-0 w-16">
                                @forelse($allImages as $i => $src)
                                <div class="rpdp-thumb w-16 h-16"
                                     :class="activeImg === {{ $i }} ? 'active' : ''"
                                     @click="activeImg = {{ $i }}">
                                    <img src="{{ $src }}" class="w-full h-full object-cover" alt="" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" onerror="this.parentElement.style.display='none'" />
                                </div>
                                @empty
                                @endforelse
                            </div>

                            {{-- Main image area --}}
                            <div class="flex-1 flex flex-col gap-3">
                                <div class="relative aspect-square bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl overflow-hidden">
                                    @if($allImages->count())
                                        @foreach($allImages as $i => $src)
                                        <img src="{{ $src }}"
                                             x-show="activeImg === {{ $i }}"
                                             {{ $i === 0 ? '' : 'x-cloak' }}
                                             alt="{{ $name }}"
                                             loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                             class="w-full h-full object-contain p-2"
                                             onerror="this.style.display='none'" />
                                        @endforeach
                                        {{-- Image counter --}}
                                        @if($allImages->count() > 1)
                                        <div class="absolute bottom-2 right-2 bg-black/40 text-white text-xs px-2 py-0.5 rounded-full backdrop-blur-sm tabular-nums"
                                             x-text="(activeImg + 1) + '/{{ $allImages->count() }}'"></div>
                                        @endif
                                    @else
                                    <div class="w-full h-full flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 rounded-2xl bg-brand/10 flex items-center justify-center mb-3">
                                            <svg class="w-10 h-10 text-brand/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                                        </div>
                                        <p class="text-xs text-slate-400 font-mono tracking-widest">{{ $product->sku }}</p>
                                    </div>
                                    @endif

                                    {{-- Out-of-stock overlay --}}
                                    @if($stockTracked && !$inStock)
                                    <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                                        <span class="bg-slate-700 text-white text-sm font-bold px-5 py-2 rounded-full uppercase tracking-widest">Out of Stock</span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Mobile horizontal thumbnails --}}
                                @if($allImages->count() > 1)
                                <div class="flex gap-2 overflow-x-auto lg:hidden pb-1">
                                    @foreach($allImages as $i => $src)
                                    <div class="rpdp-thumb shrink-0 w-14 h-14"
                                         :class="activeImg === {{ $i }} ? 'active' : ''"
                                         @click="activeImg = {{ $i }}">
                                        <img src="{{ $src }}" class="w-full h-full object-cover" alt="" loading="lazy" />
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Share row --}}
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    Share &nbsp;·&nbsp;
                                    <span class="font-mono tracking-wide text-slate-300">{{ $product->sku }}</span>
                                </div>
                            </div>

                        </div>{{-- /gallery-wrap --}}
                    </div>
                </div>{{-- /gallery card --}}

            </div>{{-- /left column --}}

            {{-- ══ RIGHT COLUMN: Sticky Purchase Panel ════════════════ --}}
            <div class="w-full xl:w-1/2 shrink-0">
                <div class="rpdp-right-sticky bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="p-5 lg:p-6"
                         x-data="productPricing(@js($priceTiers ?? []), {{ (int)$moq }}, {{ (float)($price ?? 0) }}, {{ $maxQty }}, {{ (int)($qty ?? 1) }})">

                        {{-- Name --}}
                        <h1 class="text-xl font-bold text-slate-900 leading-snug mb-1">{{ $name }}</h1>
                        @if($product->name_ur && $product->name_ur !== $name)
                        <p class="text-sm text-slate-500 mb-2" dir="rtl">{{ $product->name_ur }}</p>
                        @endif

                        {{-- ── Rating + Sold Count ─────────────────── --}}
                        @if($rating !== null || $soldLabel !== null)
                        <div class="flex items-center gap-2 mb-3 mt-2">
                            @if($rating !== null)
                            <span class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-4 h-4 {{ $s <= $ratingInt ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                                <span class="text-xs text-slate-500 font-semibold ml-1">{{ number_format($rating, 1) }}</span>
                            </span>
                            @endif
                            @if($soldLabel !== null)
                            <span class="text-xs text-slate-400 {{ $rating !== null ? 'border-l border-slate-200 pl-2' : '' }}">{{ $soldLabel }} sold</span>
                            @endif
                        </div>
                        @endif

                        {{-- Badges --}}
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            @if($catName)
                            <span class="bg-slate-100 text-slate-500 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $catName }}</span>
                            @endif
                            <span class="bg-slate-100 text-slate-400 text-xs font-mono px-2.5 py-0.5 rounded-full">{{ $product->sku }}</span>
                            @if($showStockBadge)
                            @if($inStock)
                            <span class="flex items-center gap-1 bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @if($stockTracked) {{ number_format($available) }} in stock @else In Stock @endif
                            </span>
                            @else
                            <span class="flex items-center gap-1 bg-slate-100 text-slate-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Out of Stock
                            </span>
                            @endif
                            @endif {{-- showStockBadge --}}
                        </div>

                        {{-- ── Price Box ─────────────────────────────── --}}
                        <div class="rpdp-price-box rounded-xl p-4 mb-4">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-widest mb-1">Your Store Price</p>
                            @if($price)
                            <div class="flex items-end gap-3 flex-wrap">
                                <span class="text-4xl font-black text-brand tabular-nums" x-text="tiers.length ? fmt(unitPrice) : 'PKR ' + new Intl.NumberFormat('en-US',{minimumFractionDigits:2}).format({{ (float)($price ?? 0) }})"></span>
                                <span class="text-sm text-slate-400 pb-1">/ {{ $unit }}</span>
                            </div>
                            @if($moq > 1)
                            <div class="flex items-center gap-2 mt-2">
                                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-0.5 rounded-full">MOQ {{ $moq }} {{ $unit }}</span>
                                @if($pcsCarton)
                                <span class="text-xs text-slate-400">· {{ $pcsCarton }} pcs/carton</span>
                                @endif
                            </div>
                            @else
                                @if($pcsCarton)
                                <p class="text-xs text-slate-400 mt-1.5">{{ $pcsCarton }} pieces per carton</p>
                                @endif
                            @endif
                            @if($stockTracked && $available > 0 && $moq > 1)
                            <p class="text-xs text-slate-400 mt-1.5">Min order: <strong class="text-amber-600 font-semibold">{{ $moq }}</strong> · Available: <strong class="text-emerald-600 font-semibold">{{ number_format($available) }}</strong> {{ $unit }}</p>
                            @endif
                            {{-- ── Volume price tiers table ──────────────────── --}}
                            @if(!empty($priceTiers))
                            <div class="mt-3 overflow-hidden rounded-xl border border-orange-100">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-orange-50/80 border-b border-orange-100">
                                            <th class="text-left px-3 py-2 text-slate-500 font-semibold uppercase tracking-wider">Qty Range</th>
                                            <th class="text-right px-3 py-2 text-slate-500 font-semibold uppercase tracking-wider">Unit Price</th>
                                            <th class="w-6"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-orange-50/60">
                                        @foreach($priceTiers as $i => $tier)
                                        <tr class="transition-colors"
                                            :class="activeTierIdx === {{ $i }} ? 'bg-amber-50' : 'hover:bg-orange-50/40'">
                                            <td class="px-3 py-2 font-semibold text-slate-700">
                                                {{ $tier['min_qty'] }}{{ isset($tier['max_qty']) && $tier['max_qty'] !== null ? '–'.$tier['max_qty'] : '+' }} {{ $unit }}
                                                @if($tier['label'])<span class="ml-1 text-slate-400 font-normal">({{ $tier['label'] }})</span>@endif
                                            </td>
                                            <td class="px-3 py-2 text-right tabular-nums font-bold"
                                                :class="activeTierIdx === {{ $i }} ? 'text-brand' : 'text-slate-600'">
                                                PKR {{ number_format((float)$tier['price_pkr'], 2) }}
                                            </td>
                                            <td class="pr-2 py-2 text-right">
                                                <span x-show="activeTierIdx === {{ $i }}"
                                                      class="text-[9px] bg-brand text-white rounded-full px-1.5 py-0.5 font-bold leading-none">✓</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- Upsell nudge --}}
                            <p x-show="upsellMsg" x-cloak
                               x-text="upsellMsg"
                               class="text-[11px] text-blue-600 font-semibold mt-1.5 flex items-center gap-1">
                            </p>
                            @endif
                            @else
                            <p class="text-base text-slate-400 italic">Price not available — contact your account manager</p>
                            @endif
                        </div>

                        {{-- ── Supply Info Row ──────────────────────── --}}
                        <div class="grid grid-cols-2 gap-2.5 mb-4 text-center">
                            <div class="bg-slate-50 rounded-xl px-3 py-2.5">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Unit</p>
                                <p class="text-sm font-bold text-slate-800">{{ $unit }}</p>
                            </div>
                            @if($pcsCarton)
                            <div class="bg-slate-50 rounded-xl px-3 py-2.5">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Pcs/Carton</p>
                                <p class="text-sm font-bold text-slate-800">{{ number_format($pcsCarton) }}</p>
                            </div>
                            @endif
                            @if($catName)
                            <div class="bg-slate-50 rounded-xl px-3 py-2.5">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Category</p>
                                <p class="text-sm font-bold text-slate-800 leading-tight">{{ Str::limit($catName, 18) }}</p>
                            </div>
                            @endif
                            <div class="bg-slate-50 rounded-xl px-3 py-2.5">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Origin</p>
                                <p class="text-sm font-bold text-slate-800">China</p>
                            </div>
                        </div>

                        {{-- ── Trust Badges ─────────────────────────── --}}
                        <div class="flex flex-wrap gap-3 mb-5 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Quality Assured
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Secure Payment
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                                B2B Direct
                            </span>
                        </div>

                        {{-- ── ADD TO CART SECTION ─────────────────── --}}
                        @if($price && $inStock)
                        <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Add to Cart</p>

                            @if($hasVariants)
                            <div class="space-y-4 mb-4">
                                @foreach($product->variantTypes->filter(fn($t) => $t->activeOptions->isNotEmpty()) as $variantType)
                                <div>
                                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">{{ $variantType->name }}</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($variantType->activeOptions as $option)
                                        @php $optSelected = (int) ($variantQtys[$option->id] ?? 0) > 0; @endphp
                                        <div class="rpdp-variant-card {{ $optSelected ? 'selected' : '' }} flex flex-col">
                                            <div class="px-3 pt-2.5 pb-1.5 flex items-start justify-between gap-1 min-h-[42px]">
                                                <span class="text-sm font-semibold text-slate-800 leading-tight">{{ $option->value }}</span>
                                                @if($option->price_adjustment_pkr != 0)
                                                <span class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded-full leading-tight {{ $option->price_adjustment_pkr > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-700' }}">
                                                    {{ $option->price_adjustment_pkr > 0 ? '+' : '' }}{{ \App\Services\CurrencyService::format((float)$option->price_adjustment_pkr) }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="mt-auto px-2 pb-1.5 flex items-center gap-1">
                                                <button type="button" wire:click="decrementVariant({{ $option->id }})"
                                                        class="w-7 h-7 shrink-0 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-100 text-slate-500 font-bold text-sm flex items-center justify-center transition">−</button>
                                                <input type="number"
                                                       wire:model.lazy="variantQtys.{{ $option->id }}"
                                                       min="0"
                                                       class="min-w-0 flex-1 text-center text-sm font-bold border border-slate-200 rounded-lg h-7 focus:ring-1 focus:ring-brand focus:outline-none tabular-nums bg-white" />
                                                <button type="button" wire:click="incrementVariant({{ $option->id }})"
                                                        class="w-7 h-7 shrink-0 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-100 text-slate-500 font-bold text-sm flex items-center justify-center transition">+</button>
                                            </div>
                                            <p class="text-[10px] text-slate-400 text-center pb-2 leading-none">{{ $unit }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <button wire:click="addToCart"
                                    wire:loading.attr="disabled"
                                    class="w-full py-3 rounded-xl text-base font-bold bg-brand text-white hover:bg-brand-dark active:scale-95 transition shadow-md flex items-center justify-center gap-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="addToCart" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Add Selected Variants to Cart
                                </span>
                                <span wire:loading wire:target="addToCart" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Adding…
                                </span>
                            </button>
                            <p class="text-xs text-slate-400 mt-2 text-center">Enter 0 to skip a variant option</p>

                            @else
                            <div>
                                <div class="flex items-stretch gap-3 mb-3">
                                    <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden bg-white">
                                        <button type="button"
                                                @click="qty = Math.max(moq, qty - 1)"
                                                class="w-11 h-12 text-slate-500 hover:bg-orange-50 text-xl font-bold transition flex items-center justify-center border-r border-slate-200">−</button>
                                        <input type="number"
                                               x-model.number="qty"
                                               min="{{ $moq }}"
                                               max="{{ $maxQty }}"
                                               class="w-16 h-12 text-center text-lg font-bold text-slate-800 border-0 focus:outline-none bg-white tabular-nums" />
                                        <button type="button"
                                                @click="qty = Math.min(maxQty, qty + 1)"
                                                class="w-11 h-12 text-slate-500 hover:bg-orange-50 text-xl font-bold transition flex items-center justify-center border-l border-slate-200">+</button>
                                    </div>

                                    <button wire:click="addToCart"
                                            wire:loading.attr="disabled"
                                            class="flex-1 py-3 rounded-xl text-base font-bold bg-brand text-white hover:bg-brand-dark active:scale-95 transition shadow-md flex items-center justify-center gap-2 disabled:opacity-60">
                                        <span wire:loading.remove wire:target="addToCart" class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            Add to Cart
                                        </span>
                                        <span wire:loading wire:target="addToCart" class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            Adding…
                                        </span>
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                    @if($moq > 1)
                                    <p class="text-xs text-amber-600 font-medium">Min. order: {{ $moq }} {{ $unit }}</p>
                                    @endif
                                    @if($stockTracked)
                                    <p class="text-xs text-slate-400">Max available: {{ number_format($available) }} {{ $unit }}
                                        @if($pcsCarton) · <span x-text="Math.ceil(qty / {{ $pcsCarton }})"></span> carton(s) @endif
                                    </p>
                                    @elseif($pcsCarton)
                                    <p class="text-xs text-slate-400">Cartons needed: <span x-text="Math.ceil(qty / {{ $pcsCarton }})"></span></p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        @else
                        <div class="bg-slate-100 rounded-2xl p-5 text-center">
                            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-slate-600 font-semibold text-sm">{{ !$price ? 'Price Not Available' : 'Out of Stock' }}</p>
                            <p class="text-xs text-slate-400 mt-1">Contact your account manager for assistance.</p>
                        </div>
                        @endif


                    {{-- ── Ask Seller (Chat) ──────────────────────────── --}}
                    {{-- ── Compare button ──────────────────────────────────────── --}}
                    <div class="mt-4"
                         x-data="{
                            productData: {
                                id: {{ $product->id }},
                                name: @js(Str::limit($name, 60)),
                                image: @js($allImages->first() ?? '')
                            }
                         }">
                        <button
                            @click="$store.compare.toggle(productData)"
                            :class="$store.compare.has({{ $product->id }})
                                ? 'bg-brand text-white border-brand'
                                : 'bg-white text-slate-600 border-slate-200 hover:border-brand hover:text-brand'"
                            :disabled="!$store.compare.has({{ $product->id }}) && $store.compare.items.length >= 4"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border-2 rounded-xl text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-text="$store.compare.has({{ $product->id }}) ? '✓ Added to Compare' : 'Add to Compare'"></span>
                        </button>
                        <p x-show="!$store.compare.has({{ $product->id }}) && $store.compare.items.length >= 4"
                           x-cloak
                           class="text-xs text-amber-600 text-center mt-1.5">
                            Compare list is full (4 max). Remove a product first.
                        </p>
                    </div>

                    @livewire('retailer.chat.product-chat', ['product' => $product])

                    </div>{{-- /panel inner --}}
                </div>{{-- /sticky panel --}}
            </div>{{-- /right column --}}

        </div>{{-- /two-column layout --}}

        {{-- ── Full-Width Tabs (below 50/50 columns) ──────────────── --}}
        <div class="w-full mt-5">
            {{-- ── Tabs Card ─────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                <div class="border-b border-slate-100 overflow-x-auto">
                    <div class="flex px-4 min-w-max">
                        @foreach([
                            ['key' => 'attributes', 'label' => 'Attributes & Info'],
                            ['key' => 'packing',    'label' => 'Packing & Shipping'],
                            ['key' => 'details',    'label' => 'Product Details'],
                            ['key' => 'reviews',    'label' => 'Reviews'],
                        ] as $tab)
                        <button type="button"
                                class="rpdp-tab-btn px-5 py-4 text-sm text-slate-500 hover:text-brand"
                                :class="activeTab === '{{ $tab['key'] }}' ? 'active' : ''"
                                @click="activeTab = '{{ $tab['key'] }}'">
                            {{ $tab['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- ── TAB: Attributes ─────────────────────────── --}}
                <div x-show="activeTab === 'attributes'" class="p-5 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        {{-- Attribute table --}}
                        <div>
                            <h3 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Product Specifications</h3>
                            <div class="rounded-xl overflow-hidden border border-slate-100">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-100">
                                    @php $attrs = [
                                        ['label' => 'SKU / Item No.',    'value' => $product->sku],
                                        ['label' => 'Category',          'value' => $catName],
                                        ['label' => 'Unit',              'value' => $unit],
                                        ['label' => 'Pieces Per Carton', 'value' => $pcsCarton ? number_format($pcsCarton) : null],
                                        ['label' => 'Min. Order Qty',    'value' => $moq . ' ' . $unit],
                                        ['label' => 'Origin',            'value' => 'China (Huashu)'],
                                        ['label' => 'Payment Terms',     'value' => 'PKR — Prepaid via OZ Portal'],
                                        ['label' => 'Lead Time',         'value' => '3 – 7 Business Days'],
                                    ]; @endphp
                                    @foreach($attrs as $row)
                                    @if($row['value'])
                                    <tr class="rpdp-attr-row">
                                        <td class="text-slate-400 font-medium w-2/5 bg-slate-50/70">{{ $row['label'] }}</td>
                                        <td class="text-slate-800 font-semibold">{{ $row['value'] }}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <h3 class="text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">Description</h3>
                            @php
                                $descText = $isZh && ($product->description_zh ?? null)
                                    ? $product->description_zh
                                    : ($product->description_en ?? null);
                            @endphp
                            @if($descText)
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $descText }}</p>
                            @else
                            <p class="text-sm text-slate-400 italic">No description available for this product.</p>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── TAB: Packing ──────────────────────────────── --}}
                <div x-show="activeTab === 'packing'" x-cloak class="p-5 lg:p-8">
                    <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">Packing & Variant Details</h3>

                    @if($hasVariants)
                    <div class="overflow-x-auto rounded-xl border border-slate-100 mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    @foreach($product->variantTypes->filter(fn($t) => $t->activeOptions->isNotEmpty()) as $vt)
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $vt->name }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Price Adjustment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($product->variantTypes->filter(fn($t) => $t->activeOptions->isNotEmpty()) as $vt)
                                @foreach($vt->activeOptions as $opt)
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $opt->value }}</td>
                                    @foreach($product->variantTypes->filter(fn($t) => $t->activeOptions->isNotEmpty())->skip(1) as $dummy)
                                    <td class="px-4 py-3 text-slate-400">—</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $opt->price_adjustment_pkr > 0 ? 'text-rose-600' : ($opt->price_adjustment_pkr < 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                                        @if($opt->price_adjustment_pkr == 0) No adjustment
                                        @else {{ $opt->price_adjustment_pkr > 0 ? '+' : '' }}{{ \App\Services\CurrencyService::format((float)$opt->price_adjustment_pkr) }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-slate-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-brand/10 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Carton Size</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $pcsCarton ? number_format($pcsCarton).' pcs' : 'Contact us' }}</p>
                                <p class="text-xs text-slate-400">per carton unit</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Shipping</p>
                                <p class="text-sm font-semibold text-slate-800">3–7 Business Days</p>
                                <p class="text-xs text-slate-400">China → Pakistan</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Quality</p>
                                <p class="text-sm font-semibold text-slate-800">OZ Verified</p>
                                <p class="text-xs text-slate-400">Huashu sourced</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── TAB: Product Details ───────────────────────── --}}
                <div x-show="activeTab === 'details'" x-cloak class="p-5 lg:p-8">
                    <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">Product Images & Description</h3>

                    @if($allImages->count())
                    <div class="flex flex-col gap-6 mb-6">
                        @foreach($allImages as $src)
                        <img src="{{ $src }}" alt="{{ $name }}" loading="lazy"
                             class="w-full max-w-2xl mx-auto rounded-xl shadow-sm object-contain"
                             onerror="this.remove()" />
                        @endforeach
                    </div>
                    @endif

                    @if($descText ?? null)
                    <div class="prose prose-sm max-w-none text-slate-600">
                        <p>{{ $descText }}</p>
                    </div>
                    @else
                    <p class="text-sm text-slate-400 italic text-center py-8">No additional details available for this product.</p>
                    @endif
                </div>

                {{-- ── TAB: Reviews ──────────────────────────────── --}}
                <div x-show="activeTab === 'reviews'" x-cloak class="p-5 lg:p-8">
                    @php
                        try {
                            $reviews   = $product->reviews()->get();
                        } catch (\Throwable $e) {
                            // product_reviews table may not exist yet — show empty state
                            $reviews = collect();
                        }
                        $avgRating = $reviews->avg('rating') ?? 0;
                        $total     = $reviews->count();
                        $rounded   = round($avgRating, 1);
                    @endphp

                    @if($total > 0)
                    {{-- ── Summary tile ───────────────────────────── --}}
                    <div class="flex flex-col sm:flex-row gap-8 mb-8">
                        <div class="sm:w-44 text-center shrink-0">
                            <p class="text-6xl font-black text-slate-900 leading-none mb-1">{{ number_format($rounded, 1) }}</p>
                            <div class="flex justify-center gap-0.5 mb-1">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-5 h-5 {{ $s <= round($rounded) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-xs text-slate-400">{{ $total }} {{ Str::plural('review', $total) }}</p>
                        </div>
                        {{-- rating bars --}}
                        <div class="flex-1 flex flex-col justify-center gap-1.5">
                            @for($star = 5; $star >= 1; $star--)
                            @php $count = $reviews->where('rating', $star)->count(); $pct = $total ? round($count / $total * 100) : 0; @endphp
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="w-4 text-right font-semibold">{{ $star }}</span>
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-amber-400 h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="w-8 text-right">{{ $count }}</span>
                            </div>
                            @endfor
                        </div>
                    </div>

                    {{-- ── Individual reviews ─────────────────────── --}}
                    <div class="divide-y divide-slate-100">
                        @foreach($reviews as $review)
                        <div class="py-5 first:pt-0">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand/10 flex items-center justify-center shrink-0 text-brand font-bold text-sm">
                                    {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <div class="flex gap-0.5">
                                            @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        @if($review->title)
                                        <span class="text-sm font-semibold text-slate-800">{{ $review->title }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mb-2">
                                        <span class="font-medium text-slate-700">{{ $review->reviewer_name }}</span>
                                        @if($review->reviewer_location)
                                        · {{ $review->reviewer_location }}
                                        @endif
                                        @if($review->verified_purchase)
                                        · <span class="text-emerald-600 font-medium">✓ Verified Purchase</span>
                                        @endif
                                        · <span>{{ $review->created_at->diffForHumans() }}</span>
                                    </p>
                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $review->body }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @else
                    {{-- empty state --}}
                    <div class="flex flex-col items-center justify-center text-center py-12 border-2 border-dashed border-slate-200 rounded-xl">
                        <svg class="w-10 h-10 text-brand/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <p class="text-sm font-semibold text-slate-700 mb-1">No Reviews Yet</p>
                        <p class="text-xs text-slate-400">Purchase this product and share your experience.</p>
                    </div>
                    @endif

                    {{-- ── Write a review ──────────────────────────── --}}
                    @if($reviewSubmitted)
                    <div class="mt-6 p-5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-sm font-semibold text-emerald-700">Thank you! Your review has been submitted.</p>
                    </div>
                    @elseif($retailerReviewCount >= $reviewLimit)
                    <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Review limit reached</p>
                            <p class="text-xs text-amber-700 mt-0.5">
                                You have submitted {{ $retailerReviewCount }} {{ Str::plural('review', $retailerReviewCount) }} for this product
                                (maximum allowed: {{ $reviewLimit }}).
                            </p>
                        </div>
                    </div>
                    @else
                    <div class="mt-8 border-t border-slate-100 pt-7">
                        <h4 class="text-sm font-bold text-slate-800 mb-5">Write a Review</h4>

                        {{-- Star picker --}}
                        <div class="mb-4">
                            <p class="text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wide">Your Rating <span class="text-red-400">*</span></p>
                            <div class="flex gap-1" x-data="{ hovered: 0, rating: $wire.entangle('reviewRating') }">
                                @for($s = 1; $s <= 5; $s++)
                                <button type="button"
                                    @mouseover="hovered = {{ $s }}"
                                    @mouseleave="hovered = 0"
                                    @click="rating = {{ $s }}"
                                    class="transition-transform hover:scale-110 focus:outline-none"
                                    title="{{ $s }} star{{ $s > 1 ? 's' : '' }}">
                                    <svg class="w-8 h-8 transition-colors"
                                         :class="(hovered >= {{ $s }} || rating >= {{ $s }}) ? 'text-amber-400' : 'text-slate-200'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                                @endfor
                            </div>
                            @error('reviewRating') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Title --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Review Title <span class="text-slate-400 font-normal normal-case">(optional)</span></label>
                            <input type="text"
                                   wire:model="reviewTitle"
                                   maxlength="120"
                                   placeholder="e.g. Great quality, fast delivery"
                                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand placeholder-slate-300 transition" />
                            @error('reviewTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Body --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Your Review <span class="text-red-400">*</span></label>
                            <textarea wire:model="reviewBody"
                                      rows="4"
                                      maxlength="1000"
                                      placeholder="Share your experience with this product — quality, packaging, delivery, suitability for resale..."
                                      class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand placeholder-slate-300 resize-none transition"></textarea>
                            @error('reviewBody') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <button wire:click="submitReview"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                class="inline-flex items-center gap-2 bg-brand text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-brand/90 transition focus:outline-none focus:ring-2 focus:ring-brand/40">
                            <span wire:loading.remove wire:target="submitReview">Submit Review</span>
                            <span wire:loading wire:target="submitReview" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                Submitting…
                            </span>
                        </button>
                    </div>
                    @endif
                </div>

            </div>{{-- /tabs card --}}
        </div>{{-- /full-width tabs --}}

        {{-- ══ SUPPLIER CARD ════════════════════════════════════════════ --}}
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl overflow-hidden mt-5 mb-5">
            <div class="flex items-stretch">
                <div class="bg-brand w-2 shrink-0"></div>
                <div class="flex-1 p-5 lg:p-7 flex flex-col sm:flex-row gap-5 items-start sm:items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-bold text-brand uppercase tracking-widest mb-1">Sourced via</p>
                        <h3 class="text-lg font-bold text-white mb-1">OZ Wholesale · Huashu International</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">Direct B2B wholesale from China. All products are sourced and quality-checked by OZ Tech on the Huashu platform.</p>
                        <div class="flex flex-wrap gap-3 mt-3 text-xs text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Verified Supplier
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                PKR Pricing
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                B2B Only
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('retailer.catalogue') }}"
                       class="shrink-0 bg-brand hover:bg-brand-dark text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-md">
                        Browse Catalogue →
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ RELATED PRODUCTS ════════════════════════════════════════ --}}
        @if($related->isNotEmpty())
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-brand rounded-full"></div>
                <h2 class="text-lg font-bold text-slate-800">Related Products</h2>
                <span class="text-xs text-slate-400 ml-auto">{{ $related->count() }} items</span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                @foreach($related as $rel)
                @php
                    $relPrice  = isset($commissionRates) && isset($commissionRates[$rel->id])
                        ? ($rel->min_price ? $rel->min_price * (1 + $commissionRates[$rel->id] / 100) : null)
                        : $rel->min_price;
                    $relThumb  = ($rel->images->firstWhere('is_primary', true) ?? $rel->images->first())?->display_url
                        ?? ($rel->image_path ? asset('storage/'.$rel->image_path) : null);
                    $relName   = $isZh && ($rel->name_zh ?? null) ? $rel->name_zh : $rel->name_en;
                    $relCat    = $rel->category ? ($isZh && ($rel->category->name_zh ?? null) ? $rel->category->name_zh : $rel->category->name) : null;
                    $relRating = isset($rel->rating) && $rel->rating !== null ? (float) $rel->rating : null;
                    $relRatingInt = $relRating !== null ? (int) round($relRating) : 0;
                    $relSold   = isset($rel->sold_count) && $rel->sold_count > 0
                        ? ($rel->sold_count >= 1000 ? round($rel->sold_count / 1000, 1).'k' : $rel->sold_count)
                        : null;
                @endphp
                <a href="{{ route('retailer.catalogue.product', $rel) }}"
                   class="group bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">
                    <div class="aspect-square relative overflow-hidden bg-slate-50">
                        @if($relThumb)
                        <img src="{{ $relThumb }}" alt="{{ $relName }}" loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><svg class=\'w-8 h-8 text-slate-200\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10\'/></svg></div>'" />
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        </div>
                        @endif
                        @if($relCat)
                        <span class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/55 to-transparent px-2 py-1.5 pointer-events-none">
                            <span class="text-white text-[9px] truncate block leading-tight">{{ $relCat }}</span>
                        </span>
                        @endif
                    </div>
                    <div class="p-2.5 flex flex-col flex-1">
                        <p class="text-xs font-medium text-slate-700 line-clamp-2 leading-snug flex-1 mb-1">{{ $relName }}</p>
                        @if($relRating !== null || $relSold !== null)
                        <div class="flex items-center gap-1.5 mb-1">
                            @if($relRating !== null)
                            <span class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-2.5 h-2.5 {{ $s <= $relRatingInt ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </span>
                            @endif
                            @if($relSold !== null)
                            <span class="text-[9px] text-slate-400">{{ $relSold }} sold</span>
                            @endif
                        </div>
                        @endif
                        <div class="flex items-center justify-between gap-1 mt-auto">
                            @if($relPrice)
                            <x-price :value="$relPrice" class="text-xs font-extrabold text-brand" />
                            @else
                            <span class="text-[9px] text-slate-400 italic">Ask for price</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /content --}}
</div>{{-- /outer --}}
