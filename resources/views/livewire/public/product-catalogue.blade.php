{{-- ── Public B2B Catalogue — 1688-style, no login required ───────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6">

    {{-- ══ MAIN CONTENT: SIDEBAR + PRODUCTS ═══════════════════════════════ --}}
    <div class="flex gap-0 px-4 sm:px-6 lg:px-8 mt-4 pb-10 items-start">

        {{-- ── LEFT SIDEBAR ─────────────────────────────────────────── --}}
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
                        <span class="text-xs font-normal {{ $categoryId === '' ? 'text-brand' : 'text-slate-400' }}">{{ $totalProducts }}</span>
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

            {{-- Register prompt card --}}
            <div class="mt-4 bg-brand rounded-xl p-4 text-white text-center">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-200 mb-1">{{ __('ui.wholesale_buyers') }}</p>
                <p class="text-sm font-medium mb-3">{{ __('ui.register_to_order') }}</p>
                <a href="{{ route('retailer.register') }}"
                   class="block bg-white text-brand hover:bg-orange-50 text-sm font-bold px-4 py-2 rounded-lg transition shadow">
                    {{ __('ui.apply_now') }}
                </a>
            </div>
        </aside>

        {{-- ── PRODUCT AREA ─────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Sort / results toolbar --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-4 py-3 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>
                        <strong class="text-slate-800 tabular-nums">{{ $totalProducts }}</strong> {{ __('ui.products') }}
                        @if($search) — "<em>{{ $search }}</em>" @endif
                        @if($categoryId && $categories->firstWhere('id', $categoryId)) — <strong>{{ app()->getLocale() === 'zh_CN' && $categories->firstWhere('id', $categoryId)?->name_zh ? $categories->firstWhere('id', $categoryId)?->name_zh : $categories->firstWhere('id', $categoryId)?->name }}</strong> @endif
                    </span>
                </div>

                <div class="flex items-center gap-1 text-sm">
                    <span class="text-slate-400 mr-1 text-xs">{{ __('ui.sort') }}</span>
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

            {{-- Loading indicator --}}
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
                    @php $minPrice = $product->min_price; @endphp

                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-orange-100 transition-all duration-150">

                        <a href="{{ route('public.product', $product) }}"
                           class="aspect-square bg-gradient-to-br from-slate-50 to-slate-100 relative overflow-hidden block">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                     alt="{{ $product->name_en }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-4">
                                    <div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center mb-2">
                                        <svg class="w-7 h-7 text-brand/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                    <p class="text-[10px] text-slate-300 font-mono tracking-wider">{{ $product->sku }}</p>
                                </div>
                            @endif
                            @if($product->category)
                            <div class="absolute top-2 left-2">
                                <span class="bg-white/90 backdrop-blur-sm text-slate-500 text-[10px] font-medium px-2 py-0.5 rounded-full border border-slate-100 shadow-sm">
                                    {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                                </span>
                            </div>
                            @endif
                        </a>

                        <div class="p-3 flex flex-col flex-1">
                            <a href="{{ route('public.product', $product) }}" class="hover:text-brand transition">
                                <h3 class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2 mb-1 min-h-[2.5rem]">
                                    {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                </h3>
                            </a>

                            <div class="mt-1 mb-3">
                                @if($minPrice)
                                <p class="text-xl font-bold text-brand tabular-nums leading-none">
                                    {{ \App\Services\CurrencyService::format($minPrice) }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ __('ui.per_unit') }} {{ $product->unit }}
                                    @if($product->pieces_per_carton) · {{ $product->pieces_per_carton }} {{ __('ui.pcs_per_ctn') }} @endif
                                </p>
                                @else
                                <p class="text-sm text-slate-400 italic">{{ __('ui.contact_for_price') }}</p>
                                @endif
                            </div>

                            <div class="mt-auto flex gap-2">
                                <a href="{{ route('public.product', $product) }}"
                                   class="flex-1 py-2 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1.5 border border-brand text-brand hover:bg-orange-50">
                                    {{ __('ui.view_details') }}
                                </a>
                                <a href="{{ route('retailer.login') }}"
                                   class="flex-1 py-2 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1.5 bg-brand text-white hover:bg-brand-dark shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('ui.order') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
