{{-- ── Retailer Product Detail — 1688-style with Add to Cart ─────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ BREADCRUMB BAR ═════════════════════════════════════════════════ --}}
    <div class="bg-brand px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <nav class="flex items-center gap-2 text-sm text-white/80">
                <a href="{{ route('retailer.catalogue') }}" class="hover:text-white transition">{{ __('ui.nav_catalogue') }}</a>
                <svg class="w-3 h-3 text-white/40" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                @if($product->category)
                <span class="text-white/60">{{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}</span>
                <svg class="w-3 h-3 text-white/40" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                @endif
                <span class="text-white truncate max-w-xs">{{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}</span>
            </nav>

            <a href="{{ route('retailer.cart') }}"
               class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition border border-white/20 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ __('ui.nav_cart') }}
            </a>
        </div>
    </div>

    {{-- ══ ADDED TO CART FLASH ════════════════════════════════════════════ --}}
    @if(session('cart_added'))
    <div class="mx-4 sm:mx-6 lg:mx-8 mt-3"
         x-data x-init="setTimeout(() => $el.remove(), 3000)">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span><strong>{{ session('cart_added') }}</strong> added to your cart.</span>
            <a href="{{ route('retailer.cart') }}" class="ml-auto text-emerald-700 font-semibold hover:underline text-xs">View Cart →</a>
        </div>
    </div>
    @endif

    {{-- ══ MAIN CONTENT ═══════════════════════════════════════════════════ --}}
    <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-6xl mx-auto">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex flex-col lg:flex-row">

                {{-- ── LEFT: Image ─────────────────────────────────────── --}}
                <div class="lg:w-[420px] shrink-0 bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center min-h-[280px] lg:min-h-[420px] relative">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}"
                             alt="{{ $product->name_en }}"
                             class="w-full h-full object-contain p-6 max-h-[420px]" />
                    @else
                        <div class="flex flex-col items-center justify-center p-12 text-center">
                            <div class="w-24 h-24 rounded-3xl bg-brand/10 flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-brand/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                            <p class="text-sm text-slate-400 font-mono tracking-widest">{{ $product->sku }}</p>
                        </div>
                    @endif

                    @if($product->category)
                    <div class="absolute top-3 left-3">
                        <span class="bg-white/90 backdrop-blur-sm text-slate-500 text-xs font-medium px-3 py-1 rounded-full border border-slate-100 shadow-sm">
                            {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                        </span>
                    </div>
                    @endif

                    {{-- Out-of-stock overlay (only when stock is tracked and depleted) --}}
                    @if($stockTracked && $available < 1)
                    <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
                        <span class="bg-slate-700 text-white text-sm font-bold px-5 py-2 rounded-full tracking-wide uppercase">{{ __('ui.out_of_stock') }}</span>
                    </div>
                    @endif
                </div>

                {{-- ── RIGHT: Details + Add to Cart ────────────────────── --}}
                <div class="flex-1 p-6 lg:p-8 border-t lg:border-t-0 lg:border-l border-slate-100">

                    {{-- SKU --}}
                    <p class="text-xs text-slate-400 font-mono mb-2 tracking-wide">SKU: {{ $product->sku }}</p>

                    {{-- Product name --}}
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight mb-1">{{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}</h1>
                    @if($product->name_ur)
                    <p class="text-base text-slate-500 mb-3" dir="rtl">{{ $product->name_ur }}</p>
                    @endif

                    <div class="border-t border-slate-100 my-4"></div>

                    {{-- Price display (store-specific) --}}
                    <div class="mb-2">
                        @if($price)
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black text-brand tabular-nums">{{ \App\Services\CurrencyService::format($price) }}</span>
                            <span class="text-sm text-slate-400">/ {{ $product->unit }}</span>
                        </div>
                        @if($product->pieces_per_carton)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $product->pieces_per_carton }} pieces per carton</p>
                        @endif
                        @else
                        <p class="text-base text-slate-400 italic bg-slate-50 rounded-xl px-4 py-3">{{ __('ui.price_not_available') }}</p>
                        @endif
                    </div>

                    {{-- Stock status --}}
                    @php $inStock = ! $stockTracked || $available > 0; @endphp
                    <div class="flex items-center gap-2 mb-5">
                        @if($inStock)
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @if($stockTracked)
                                    {{ __('ui.in_stock') }} — {{ number_format($available) }} available
                                @else
                                    {{ __('ui.in_stock') }}
                                @endif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-500 text-xs font-semibold px-3 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                {{ __('ui.out_of_stock') }}
                            </span>
                        @endif
                    </div>

                    {{-- All store prices table --}}
                    @if($product->storePrices->count() > 1)
                    <div class="mb-5">
                        <button onclick="document.getElementById('price-table').classList.toggle('hidden')"
                                class="text-xs text-brand hover:underline flex items-center gap-1 mb-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('ui.view_all_prices') }}
                        </button>
                        <div id="price-table" class="hidden">
                            <div class="bg-slate-50 rounded-xl overflow-hidden border border-slate-100 text-sm">
                                <table class="w-full">
                                    <tbody class="divide-y divide-slate-100">
                                    @foreach($product->storePrices as $sp)
                                    <tr class="hover:bg-orange-50 transition">
                                        <td class="px-4 py-2 text-slate-600">{{ $sp->store_id }}</td>
                                        <td class="px-4 py-2 text-right font-bold tabular-nums text-slate-800">{{ \App\Services\CurrencyService::format($sp->price_pkr) }}</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Specs --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                        <div class="bg-slate-50 rounded-xl px-4 py-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">{{ __('ui.unit') }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ $product->unit }}</p>
                        </div>
                        @if($product->pieces_per_carton)
                        <div class="bg-slate-50 rounded-xl px-4 py-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Pcs / Carton</p>
                            <p class="text-sm font-bold text-slate-800">{{ number_format($product->pieces_per_carton) }}</p>
                        </div>
                        @endif
                        @if($product->category)
                        <div class="bg-slate-50 rounded-xl px-4 py-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-0.5">{{ __('ui.category') }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($product->description_en)
                    <div class="mb-6">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">{{ __('ui.description') }}</p>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $product->description_en }}</p>
                    </div>
                    @endif

                    {{-- ─────── ADD TO CART ─────── --}}
                    @php $maxQty = $stockTracked ? $available : 9999; @endphp
                    @if($price && $inStock)
                    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">{{ __('ui.add_to_cart') }}</p>

                        @if($hasVariants)
                        {{-- ── VARIANT QTY SELECTORS ── --}}
                        <div class="space-y-4 mb-4">
                            @foreach($product->variantTypes->filter(fn($t) => $t->activeOptions->isNotEmpty()) as $variantType)
                            <div>
                                <p class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">{{ $variantType->name }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 items-stretch">
                                    @foreach($variantType->activeOptions as $option)
                                    @php $optionSelected = (int) ($variantQtys[$option->id] ?? 0) > 0; @endphp
                                    <div class="rounded-xl border flex flex-col transition-colors {{ $optionSelected ? 'bg-orange-50 border-brand ring-1 ring-brand' : 'bg-white border-slate-200' }}">
                                        {{-- Label row: fixed min-height keeps cards same size regardless of price badge --}}
                                        <div class="px-3 pt-3 pb-2 flex items-start justify-between gap-1 min-h-[44px]">
                                            <span class="text-sm font-semibold text-slate-800 leading-tight">{{ $option->value }}</span>
                                            @if($option->price_adjustment_pkr != 0)
                                            <span class="shrink-0 text-[10px] font-semibold leading-tight px-1.5 py-0.5 rounded-full {{ $option->price_adjustment_pkr > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-700' }}">
                                                {{ $option->price_adjustment_pkr > 0 ? '+' : '' }}{{ \App\Services\CurrencyService::format((float) $option->price_adjustment_pkr) }}
                                            </span>
                                            @endif
                                        </div>
                                        {{-- Stepper: push to bottom via mt-auto --}}
                                        <div class="mt-auto px-2 pb-1.5 flex items-center gap-1">
                                            <button type="button"
                                                    wire:click="decrementVariant({{ $option->id }})"
                                                    class="w-7 h-7 shrink-0 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-100 text-slate-500 font-bold text-sm flex items-center justify-center transition">−</button>
                                            <input type="number"
                                                   wire:model.lazy="variantQtys.{{ $option->id }}"
                                                   min="0"
                                                   class="min-w-0 flex-1 text-center text-sm font-bold border border-slate-200 rounded-lg h-7 focus:ring-1 focus:ring-brand focus:outline-none tabular-nums bg-white" />
                                            <button type="button"
                                                    wire:click="incrementVariant({{ $option->id }})"
                                                    class="w-7 h-7 shrink-0 rounded-lg border border-slate-200 bg-slate-50 hover:bg-orange-100 text-slate-500 font-bold text-sm flex items-center justify-center transition">+</button>
                                        </div>
                                        <p class="text-[10px] text-slate-400 text-center pb-2 leading-none">{{ $product->unit }}</p>
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Add Selected Variants to Cart
                            </span>
                            <span wire:loading wire:target="addToCart" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Adding...
                            </span>
                        </button>
                        <p class="text-xs text-slate-400 mt-2 text-center">Enter 0 to skip a variant option</p>

                        @else
                        {{-- ── SIMPLE QTY + ADD ── --}}
                        <div class="flex items-stretch gap-3">
                            <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden bg-white">
                                <button type="button"
                                        onclick="var v=parseInt(this.nextElementSibling.value)||1; var lc=this.closest('[wire:id]'); window.Livewire.find(lc.getAttribute('wire:id')).set('qty', Math.max({{ $moq }},v-1))"
                                        class="w-10 h-12 text-slate-500 hover:bg-slate-50 text-xl font-bold transition flex items-center justify-center border-r border-slate-200">
                                    -
                                </button>
                                <input type="number"
                                       wire:model="qty"
                                       min="{{ $moq }}"
                                       max="{{ $maxQty }}"
                                       class="w-16 h-12 text-center text-lg font-bold text-slate-800 border-0 focus:outline-none bg-white tabular-nums" />
                                <button type="button"
                                        onclick="var v=parseInt(this.previousElementSibling.value)||1; var max={{ $maxQty }}; var lc=this.closest('[wire:id]'); window.Livewire.find(lc.getAttribute('wire:id')).set('qty', Math.min(max,v+1))"
                                        class="w-10 h-12 text-slate-500 hover:bg-slate-50 text-xl font-bold transition flex items-center justify-center border-l border-slate-200">
                                    +
                                </button>
                            </div>

                            <button wire:click="addToCart"
                                    wire:loading.attr="disabled"
                                    class="flex-1 py-3 rounded-xl text-base font-bold bg-brand text-white hover:bg-brand-dark active:scale-95 transition shadow-md flex items-center justify-center gap-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="addToCart" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ __('ui.add_to_cart') }}
                                </span>
                                <span wire:loading wire:target="addToCart" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    {{ __('ui.adding') }}
                                </span>
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2">
                            @if($moq > 1)
                            <p class="text-xs text-amber-600 font-medium">Min. {{ $moq }} {{ $product->unit }} required</p>
                            @endif
                            @if($stockTracked)
                            <p class="text-xs text-slate-400">Max: {{ number_format($available) }} {{ $product->unit }}@if($product->pieces_per_carton) &middot; {{ ceil($qty / $product->pieces_per_carton) }} carton(s)@endif</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="bg-slate-100 rounded-2xl p-5 text-center">
                        <p class="text-slate-500 font-semibold">
                            {{ !$price ? __('ui.price_not_available') : __('ui.out_of_stock') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">{{ __('ui.contact_account_manager') }}</p>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ══ RELATED PRODUCTS ═══════════════════════════════════════════ --}}
        @if($related->isNotEmpty())
        <div class="mt-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-brand rounded-full"></div>
                <h2 class="text-lg font-bold text-slate-800">{{ __('ui.related_products') }}</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($related as $rel)
                @php $relPrice = $rel->min_price; @endphp
                <a href="{{ route('retailer.catalogue.product', $rel) }}"
                   class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-orange-100 transition-all duration-150">

                    <div class="aspect-square bg-gradient-to-br from-slate-50 to-slate-100 relative overflow-hidden">
                        @if($rel->image_path)
                            <img src="{{ asset('storage/' . $rel->image_path) }}"
                                 alt="{{ $rel->name_en }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-brand/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2 mb-1">{{ app()->getLocale() === 'zh_CN' && $rel->name_zh ? $rel->name_zh : $rel->name_en }}</h3>
                        @if($relPrice)
                        <p class="text-sm font-bold text-brand tabular-nums">{{ \App\Services\CurrencyService::format($relPrice) }}</p>
                        @else
                        <p class="text-xs text-slate-400 italic">{{ __('ui.contact_for_price') }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
