{{-- ── Retailer Home — 1688-style: categories sidebar + compact strips + all products ─── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ MAIN LAYOUT: CATEGORY SIDEBAR + CONTENT ════════════════════════ --}}
    <div class="flex gap-0 px-4 sm:px-6 lg:px-8 mt-4 pb-10 items-start">

        {{-- ── LEFT: CATEGORY SIDEBAR ─────────────────────────────────── --}}
        <aside class="w-52 shrink-0 mr-5 hidden md:block sticky top-20 self-start">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-brand px-4 py-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-white/70" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    <h2 class="text-white font-semibold text-sm tracking-wide uppercase">{{ __('ui.categories') }}</h2>
                </div>
                <nav class="divide-y divide-slate-50">
                    @foreach($categories as $cat)
                    <a href="{{ route('retailer.catalogue', ['category' => $cat->id]) }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-orange-50 hover:text-brand border-l-4 border-transparent hover:border-brand transition-all">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="truncate">{{ app()->getLocale() === 'zh_CN' && $cat->name_zh ? $cat->name_zh : $cat->name }}</span>
                    </a>
                    @endforeach
                    <a href="{{ route('retailer.catalogue') }}"
                       class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-semibold text-brand hover:bg-orange-50 transition">
                        {{ __('ui.all_products') }} →
                    </a>
                </nav>
            </div>

            {{-- Quick links --}}
            <div class="mt-4 space-y-2">
                <a href="{{ route('retailer.cart') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:text-brand hover:border-brand transition shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('ui.cart') }}
                </a>
                <a href="{{ route('retailer.orders') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:text-brand hover:border-brand transition shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('ui.my_orders') }}
                </a>
            </div>
        </aside>

        {{-- ── RIGHT: CONTENT AREA ─────────────────────────────────────── --}}
        <div class="flex-1 min-w-0 space-y-4">

            {{-- ── DEALS STRIP ──────────────────────────────────────────── --}}
            @if($sections['deals'] && $deals->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                {{-- Strip header --}}
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-brand/10">
                            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </span>
                        <span class="text-sm font-bold text-slate-800">{{ __('ui.sourcing_top_deals') }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ $deals->count() }} {{ __('ui.products') }}</span>
                    </div>
                    <a href="{{ route('retailer.catalogue') }}" class="text-xs text-brand hover:underline font-medium">{{ __('ui.view_all_deals') }}</a>
                </div>
                {{-- Horizontal scroll row --}}
                <div class="flex gap-2 overflow-x-auto px-4 py-3 scrollbar-hide">
                    @foreach($deals as $product)
                    @php $retailPrice = $retailerPrices[$product->id] ?? null; @endphp
                    <a href="{{ route('retailer.catalogue.product', $product) }}"
                       class="w-[110px] shrink-0 group">
                        <div class="w-[110px] h-[110px] rounded-lg overflow-hidden bg-slate-50 relative">
                            @if($product->primaryImage())
                                <img src="{{ $product->primaryImage()->display_url }}"
                                     alt="{{ $product->name_en }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"/>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="absolute top-1 left-1 bg-brand text-white text-[8px] font-bold px-1 py-0.5 rounded uppercase">Deal</span>
                        </div>
                        <p class="text-[11px] text-slate-700 font-medium line-clamp-2 leading-snug mt-1.5">
                            {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                        </p>
                        @if($retailPrice)
                        <p class="text-[12px] font-bold text-brand mt-0.5 tabular-nums">{{ \App\Services\CurrencyService::format($retailPrice) }}</p>
                        @else
                        <p class="text-[10px] text-slate-400 italic mt-0.5">{{ __('ui.contact_for_price') }}</p>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── NEW ARRIVALS STRIP ──────────────────────────────────── --}}
            @if($sections['new_arrivals'] && $newArrivals->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-50">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </span>
                        <span class="text-sm font-bold text-slate-800">{{ __('ui.new_arrivals') }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ $newArrivals->count() }} {{ __('ui.products') }}</span>
                    </div>
                    <a href="{{ route('retailer.catalogue') }}" class="text-xs text-brand hover:underline font-medium">{{ __('ui.view_all') }} →</a>
                </div>
                <div class="flex gap-2 overflow-x-auto px-4 py-3 scrollbar-hide">
                    @foreach($newArrivals as $product)
                    @php $retailPrice = $retailerPrices[$product->id] ?? null; @endphp
                    <a href="{{ route('retailer.catalogue.product', $product) }}"
                       class="w-[110px] shrink-0 group">
                        <div class="w-[110px] h-[110px] rounded-lg overflow-hidden bg-slate-50 relative">
                            @if($product->primaryImage())
                                <img src="{{ $product->primaryImage()->display_url }}"
                                     alt="{{ $product->name_en }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"/>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="absolute top-1 left-1 bg-emerald-500 text-white text-[8px] font-bold px-1 py-0.5 rounded uppercase">New</span>
                        </div>
                        <p class="text-[11px] text-slate-700 font-medium line-clamp-2 leading-snug mt-1.5">
                            {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                        </p>
                        @if($retailPrice)
                        <p class="text-[12px] font-bold text-brand mt-0.5 tabular-nums">{{ \App\Services\CurrencyService::format($retailPrice) }}</p>
                        @else
                        <p class="text-[10px] text-slate-400 italic mt-0.5">{{ __('ui.contact_for_price') }}</p>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── ALL PRODUCTS GRID ─────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="text-sm font-bold text-slate-800">{{ __('ui.all_products') }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ number_format($allProducts->total()) }} {{ __('ui.products') }}</span>
                    </div>
                    <a href="{{ route('retailer.catalogue') }}" class="text-xs text-brand hover:underline font-medium">{{ __('ui.view_all') }} →</a>
                </div>

                @if($allProducts->isEmpty())
                <div class="py-16 text-center text-slate-400 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                    No products yet.
                </div>
                @else
                <div class="p-4">
                    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                        @foreach($allProducts as $product)
                        @php $retailPrice = $retailerPrices[$product->id] ?? null; @endphp
                        <a href="{{ route('retailer.catalogue.product', $product) }}"
                           class="group bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">
                            <div class="aspect-square relative overflow-hidden bg-slate-50">
                                @if($product->primaryImage())
                                    <img src="{{ $product->primaryImage()->display_url }}"
                                         alt="{{ $product->name_en }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         loading="lazy"/>
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-1"
                                         style="background: linear-gradient(135deg, #fff5f0 0%, #fff 100%);">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-brand">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                                @if($product->is_deal ?? false)
                                <span class="absolute top-1.5 left-1.5 bg-brand text-white text-[9px] font-bold px-1.5 py-0.5 rounded uppercase">Deal</span>
                                @endif
                                @if($product->category)
                                <span class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-1.5">
                                    <span class="text-white text-[9px] truncate block">{{ $product->category->name }}</span>
                                </span>
                                @endif
                            </div>
                            <div class="p-2.5 flex flex-col flex-1">
                                <p class="text-xs font-medium text-slate-700 line-clamp-2 leading-snug flex-1">
                                    {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                </p>
                                <div class="mt-1.5 pt-1.5 border-t border-slate-50">
                                    @if($retailPrice)
                                    <p class="text-sm font-extrabold text-brand tabular-nums">{{ \App\Services\CurrencyService::format($retailPrice) }}</p>
                                    @else
                                    <p class="text-xs text-slate-400 italic">{{ __('ui.contact_for_price') }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    @if($allProducts->hasPages())
                    <div class="mt-6 flex justify-center">
                        {{ $allProducts->links() }}
                    </div>
                    @endif
                </div>
                @endif
            </div>

        </div>{{-- end right content --}}
    </div>{{-- end main layout --}}

</div>
