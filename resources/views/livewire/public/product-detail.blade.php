{{-- ── Public Product Detail Page (1688-style, no login required) ──── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ BREADCRUMB BAR ═════════════════════════════════════════════════ --}}
    <div class="bg-brand px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-white/80 max-w-6xl mx-auto">
            <a href="{{ route('public.catalogue') }}" class="hover:text-white transition">{{ __('ui.home') }}</a>
            <svg class="w-3 h-3 text-white/40" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
            @if($product->category)
            <a href="{{ route('public.catalogue') }}?categoryId={{ $product->category_id }}" class="hover:text-white transition">{{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}</a>
            <svg class="w-3 h-3 text-white/40" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
            @endif
            <span class="text-white truncate max-w-xs">{{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}</span>
        </nav>
    </div>

    {{-- ══ MAIN PRODUCT PANEL ══════════════════════════════════════════════ --}}
    <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-6xl mx-auto">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex flex-col lg:flex-row gap-0">

                {{-- ── LEFT: Product Image ──────────────────────────────── --}}
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

                    {{-- Category badge --}}
                    @if($product->category)
                    <div class="absolute top-3 left-3">
                        <span class="bg-white/90 backdrop-blur-sm text-slate-500 text-xs font-medium px-3 py-1 rounded-full border border-slate-100 shadow-sm">
                            {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- ── RIGHT: Product Details ───────────────────────────── --}}
                <div class="flex-1 p-6 lg:p-8 border-t lg:border-t-0 lg:border-l border-slate-100">

                    {{-- SKU --}}
                    <p class="text-xs text-slate-400 font-mono mb-2 tracking-wide">SKU: {{ $product->sku }}</p>

                    {{-- Product Name --}}
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight mb-1">{{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}</h1>
                    @if($product->name_ur)
                    <p class="text-base text-slate-500 mb-4" dir="rtl">{{ $product->name_ur }}</p>
                    @endif

                    {{-- Divider --}}
                    <div class="border-t border-slate-100 my-4"></div>

                    {{-- Price table (across stores) --}}
                    @if($product->storePrices->isNotEmpty())
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">{{ __('ui.wholesale_price') }}</p>
                        <div class="bg-orange-50 border border-orange-100 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-brand/10 text-brand">
                                        <th class="text-left px-4 py-2 font-semibold text-xs uppercase tracking-wide">{{ __('ui.store_location') }}</th>
                                        <th class="text-right px-4 py-2 font-semibold text-xs uppercase tracking-wide">Price ({{ session('currency', 'PKR') }})</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-orange-100">
                                    @foreach($product->storePrices as $sp)
                                    <tr class="{{ $loop->first ? 'bg-orange-50' : 'bg-white' }} hover:bg-orange-50 transition">
                                        <td class="px-4 py-2.5 text-slate-700 font-medium">
                                            @if($loop->first)<span class="text-brand font-bold">{{ __('ui.best_price') }} — </span>@endif
                                            {{ $sp->store_id ?? 'All Stores' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-bold tabular-nums {{ $loop->first ? 'text-brand text-lg' : 'text-slate-700' }}">
                                            {{ \App\Services\CurrencyService::format($sp->price_pkr) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Per {{ $product->unit }}
                            @if($product->pieces_per_carton) · {{ $product->pieces_per_carton }} pieces per carton @endif
                        </p>
                    </div>
                    @else
                    <div class="mb-5">
                        <p class="text-sm text-slate-400 italic bg-slate-50 rounded-xl px-4 py-3">{{ __('ui.contact_wholesale') }}</p>
                    </div>
                    @endif

                    {{-- Specs row --}}
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

                    {{-- CTA: Login to Order --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <a href="{{ route('retailer.login') }}"
                           class="flex-1 py-3.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brand-dark transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('ui.sign_in_to_order') }}
                        </a>
                        <a href="{{ route('retailer.register') }}"
                           class="flex-1 py-3.5 rounded-xl text-sm font-bold border-2 border-brand text-brand hover:bg-orange-50 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            {{ __('ui.apply_wholesale') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ RELATED PRODUCTS ════════════════════════════════════════════ --}}
        @if($related->isNotEmpty())
        <div class="mt-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-brand rounded-full"></div>
                <h2 class="text-lg font-bold text-slate-800">{{ __('ui.related_products') }}</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($related as $rel)
                @php $relPrice = $rel->min_price; @endphp
                <a href="{{ route('public.product', $rel) }}"
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
