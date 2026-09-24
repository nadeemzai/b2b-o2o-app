{{-- ── Retailer Product Detail — 1688-style with live Add to Cart ─────────── --}}

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
@endphp

<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

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

        {{-- ── WHITE PRODUCT CARD ────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-visible mb-6">
            <div class="flex flex-col lg:flex-row">

                {{-- ── LEFT: Image Gallery ─────────────────────────────── --}}
                <div class="lg:w-[480px] shrink-0 p-4">
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
                </div>{{-- /left --}}

                {{-- ── RIGHT: Purchase Panel ────────────────────────── --}}
                <div class="flex-1 border-t lg:border-t-0 lg:border-l border-slate-100 lg:min-h-[520px]">
                    <div class="rpdp-right-sticky p-5 lg:p-7">

                        {{-- Name --}}
                        <h1 class="text-xl lg:text-2xl font-bold text-slate-900 leading-snug mb-1">{{ $name }}</h1>
                        @if($product->name_ur && $product->name_ur !== $name)
                        <p class="text-sm text-slate-500 mb-2" dir="rtl">{{ $product->name_ur }}</p>
                        @endif

                        {{-- Badges --}}
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            @if($catName)
                            <span class="bg-slate-100 text-slate-500 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $catName }}</span>
                            @endif
                            <span class="bg-slate-100 text-slate-400 text-xs font-mono px-2.5 py-0.5 rounded-full">{{ $product->sku }}</span>
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
                        </div>

                        {{-- ── Price Box ─────────────────────────────── --}}
                        <div class="rpdp-price-box rounded-xl p-4 mb-4">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-widest mb-1">Your Store Price</p>
                            @if($price)
                            <div class="flex items-end gap-3 flex-wrap">
                                <span class="text-4xl font-black text-brand leading-none tabular-nums">{{ \App\Services\CurrencyService::format($price) }}</span>
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
                            @else
                            <p class="text-base text-slate-400 italic">Price not available — contact your account manager</p>
                            @endif
                        </div>

                        {{-- ── Supply Info Row ──────────────────────── --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 text-center">
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
                            {{-- VARIANT CARDS with Livewire qty steppers --}}
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
                            {{-- SIMPLE QTY + ADD --}}
                            <div x-data="{ localQty: $wire.entangle('qty') }">
                                <div class="flex items-stretch gap-3 mb-3">
                                    <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden bg-white">
                                        <button type="button"
                                                @click="localQty = Math.max({{ $moq }}, localQty - 1)"
                                                class="w-11 h-12 text-slate-500 hover:bg-orange-50 text-xl font-bold transition flex items-center justify-center border-r border-slate-200">−</button>
                                        <input type="number"
                                               x-model.number="localQty"
                                               min="{{ $moq }}"
                                               max="{{ $maxQty }}"
                                               class="w-16 h-12 text-center text-lg font-bold text-slate-800 border-0 focus:outline-none bg-white tabular-nums" />
                                        <button type="button"
                                                @click="localQty = Math.min({{ $maxQty }}, localQty + 1)"
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
                                        @if($pcsCarton) · <span x-text="Math.ceil(localQty / {{ $pcsCarton }})"></span> carton(s) @endif
                                    </p>
                                    @elseif($pcsCarton)
                                    <p class="text-xs text-slate-400">Cartons needed: <span x-text="Math.ceil(localQty / {{ $pcsCarton }})"></span></p>
                                    @endif
                                </div>
                            </div>

                            @endif
                        </div>

                        @else
                        {{-- Price not available or out of stock --}}
                        <div class="bg-slate-100 rounded-2xl p-5 text-center">
                            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-slate-600 font-semibold text-sm">{{ !$price ? 'Price Not Available' : 'Out of Stock' }}</p>
                            <p class="text-xs text-slate-400 mt-1">Contact your account manager for assistance.</p>
                        </div>
                        @endif

                    </div>{{-- /sticky panel --}}
                </div>{{-- /right --}}

            </div>{{-- /flex row --}}
        </div>{{-- /white card --}}

        {{-- ══ TAB BAR ══════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">

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

            {{-- ── TAB: Attributes ─────────────────────────────────── --}}
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

            {{-- ── TAB: Packing ─────────────────────────────────────── --}}
            <div x-show="activeTab === 'packing'" x-cloak class="p-5 lg:p-8">
                <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">Packing & Variant Details</h3>

                @if($hasVariants)
                {{-- Variant matrix table --}}
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

                {{-- Packing info cards --}}
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

            {{-- ── TAB: Product Details (images + desc) ─────────────── --}}
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

            {{-- ── TAB: Reviews ─────────────────────────────────────── --}}
            <div x-show="activeTab === 'reviews'" x-cloak class="p-5 lg:p-8">
                <div class="flex flex-col sm:flex-row gap-8">
                    {{-- Star summary --}}
                    <div class="sm:w-48 text-center">
                        <p class="text-6xl font-black text-slate-900 leading-none mb-1">—</p>
                        <div class="flex justify-center gap-0.5 mb-1">
                            @for($s = 1; $s <= 5; $s++)
                            <svg class="w-5 h-5 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400">No reviews yet</p>
                    </div>
                    {{-- CTA --}}
                    <div class="flex-1 flex flex-col items-center justify-center text-center py-8 border-2 border-dashed border-slate-200 rounded-xl">
                        <svg class="w-10 h-10 text-brand/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <p class="text-sm font-semibold text-slate-700 mb-1">No Reviews Yet</p>
                        <p class="text-xs text-slate-400">Purchase this product and share your experience.</p>
                    </div>
                </div>
            </div>

        </div>{{-- /tab card --}}

        {{-- ══ SUPPLIER CARD ════════════════════════════════════════════ --}}
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl overflow-hidden mb-6">
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

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($related as $rel)
                @php
                    $relPrice  = $rel->min_price;
                    $relThumb  = ($rel->images->firstWhere('is_primary', true) ?? $rel->images->first())?->display_url
                        ?? ($rel->image_path ? asset('storage/'.$rel->image_path) : null);
                    $relName   = $isZh && ($rel->name_zh ?? null) ? $rel->name_zh : $rel->name_en;
                @endphp
                <a href="{{ route('retailer.catalogue.product', $rel) }}"
                   class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-orange-100 transition-all duration-150 relative">
                    <div class="aspect-square bg-gradient-to-br from-slate-50 to-slate-100 overflow-hidden">
                        @if($relThumb)
                        <img src="{{ $relThumb }}" alt="{{ $relName }}" loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                             onerror="this.parentElement.innerHTML=''" />
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-brand/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        </div>
                        @endif
                        <div class="absolute inset-0 bg-brand/0 group-hover:bg-brand/5 transition-colors duration-200 flex items-end justify-center pb-2 opacity-0 group-hover:opacity-100">
                            <span class="bg-brand text-white text-xs font-bold px-3 py-1 rounded-full shadow">View →</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2 mb-1.5">{{ $relName }}</h3>
                        @if($relPrice)
                        <p class="text-sm font-bold text-brand tabular-nums">{{ \App\Services\CurrencyService::format($relPrice) }}</p>
                        @else
                        <p class="text-xs text-slate-400 italic">Contact for price</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /content --}}
</div>{{-- /outer --}}
