{{-- ── Retailer Home — 1688-style: categories sidebar + featured sections ─── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ MAIN LAYOUT: CATEGORY SIDEBAR + FEATURED SECTIONS ══════════════ --}}
    <div class="flex gap-0 px-4 sm:px-6 lg:px-8 mt-4 pb-10 items-start">

        {{-- ── LEFT: CATEGORY SIDEBAR ─────────────────────────────────── --}}
        <aside class="w-52 shrink-0 mr-5 hidden md:block">
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

            {{-- Quick links for authenticated retailer --}}
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

        {{-- ── RIGHT: FEATURED SECTIONS ────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            @if(!$sections['deals'] && !$sections['new_arrivals'])
            {{-- Both off — show browse-catalogue CTA --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <p class="text-slate-700 font-semibold text-lg mb-1">Explore Our Wholesale Catalogue</p>
                <p class="text-slate-400 text-sm mb-6">Browse thousands of products at your approved wholesale rates.</p>
                <a href="{{ route('retailer.catalogue') }}"
                   class="inline-flex items-center gap-2 bg-brand text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-dark transition shadow-sm">
                    Browse Full Catalogue →
                </a>
            </div>
            @else

            {{-- ── GRID: 1 col (one section) or 2 cols (both sections) ── --}}
            <div class="{{ $sections['deals'] && $sections['new_arrivals'] ? 'grid grid-cols-1 lg:grid-cols-2 gap-4' : '' }}">

                {{-- ── SOURCING TOP DEALS ──────────────────────────── --}}
                @if($sections['deals'])
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">

                    {{-- Section header --}}
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-brand/10">
                                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                            <span class="text-sm font-bold text-slate-800 tracking-tight">{{ __('ui.sourcing_top_deals') }}</span>
                        </div>
                        <a href="{{ route('retailer.catalogue') }}" class="text-xs text-brand hover:underline font-medium">{{ __('ui.view_all') }} →</a>
                    </div>

                    @if($deals->isEmpty())
                    <div class="p-10 text-center text-slate-400 text-sm">No deals available yet.</div>
                    @else
                    <div class="grid grid-cols-2 gap-px bg-slate-100">
                        @foreach($deals as $product)
                        @php $retailPrice = $retailerPrices[$product->id] ?? null; @endphp
                        <a href="{{ route('retailer.catalogue.product', $product) }}"
                           class="bg-white p-3 flex flex-col gap-2 hover:bg-orange-50/50 transition group">
                            <div class="aspect-square rounded-lg overflow-hidden bg-slate-50 relative">
                                @if($product->primaryImage())
                                    <img src="{{ $product->primaryImage()->display_url }}"
                                         alt="{{ $product->name_en }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"/>
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute top-1.5 left-1.5 bg-brand text-white text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">Deal</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-700 font-medium line-clamp-2 leading-snug">
                                    {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                </p>
                                @if($retailPrice)
                                <p class="text-base font-bold text-brand mt-1 tabular-nums">{{ \App\Services\CurrencyService::format($retailPrice) }}</p>
                                @else
                                <p class="text-xs text-slate-400 italic mt-1">{{ __('ui.contact_for_price') }}</p>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    <div class="px-4 py-3 border-t border-slate-50 bg-slate-50/50">
                        <a href="{{ route('retailer.catalogue') }}"
                           class="flex items-center justify-center gap-2 py-2 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark transition shadow-sm w-full">
                            {{ __('ui.view_all_deals') }}
                        </a>
                    </div>
                </div>
                @endif

                {{-- ── NEW ARRIVALS ─────────────────────────────────── --}}
                @if($sections['new_arrivals'])
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">

                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </span>
                            <span class="text-sm font-bold text-slate-800 tracking-tight">{{ __('ui.new_arrivals') }}</span>
                        </div>
                        <a href="{{ route('retailer.catalogue') }}" class="text-xs text-brand hover:underline font-medium">{{ __('ui.view_all') }} →</a>
                    </div>

                    @if($newArrivals->isEmpty())
                    <div class="p-10 text-center text-slate-400 text-sm">No products yet.</div>
                    @else
                    <div class="grid grid-cols-2 gap-px bg-slate-100">
                        @foreach($newArrivals as $product)
                        @php $retailPrice = $retailerPrices[$product->id] ?? null; @endphp
                        <a href="{{ route('retailer.catalogue.product', $product) }}"
                           class="bg-white p-3 flex flex-col gap-2 hover:bg-orange-50/50 transition group">
                            <div class="aspect-square rounded-lg overflow-hidden bg-slate-50 relative">
                                @if($product->primaryImage())
                                    <img src="{{ $product->primaryImage()->display_url }}"
                                         alt="{{ $product->name_en }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"/>
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute top-1.5 left-1.5 bg-emerald-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">New</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-700 font-medium line-clamp-2 leading-snug">
                                    {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                </p>
                                @if($retailPrice)
                                <p class="text-base font-bold text-brand mt-1 tabular-nums">{{ \App\Services\CurrencyService::format($retailPrice) }}</p>
                                @else
                                <p class="text-xs text-slate-400 italic mt-1">{{ __('ui.contact_for_price') }}</p>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    <div class="px-4 py-3 border-t border-slate-50 bg-slate-50/50">
                        <a href="{{ route('retailer.catalogue') }}"
                           class="flex items-center justify-center gap-2 py-2 border border-brand text-brand text-xs font-semibold rounded-lg hover:bg-orange-50 transition w-full">
                            Browse All Products →
                        </a>
                    </div>
                </div>
                @endif

            </div>{{-- end sections grid --}}
            @endif

        </div>{{-- end right area --}}
    </div>{{-- end main layout --}}

</div>
