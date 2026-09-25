{{--
  New Arrivals Infinite Carousel
  Props:
    $products     - Collection of products (with images eager-loaded)
    $viewAllRoute - Route name for "View all" link  (e.g. 'retailer.catalogue' / 'public.catalogue')
    $productRoute - Route name for individual product (e.g. 'retailer.catalogue.product' / 'public.product')
    $priceMap     - array [product_id => price] for retailer context; null = use product->min_price
    $heading      - Heading label (default 'New Arrivals')
--}}
@props([
    'products',
    'viewAllRoute',
    'productRoute',
    'priceMap' => null,
    'heading'  => 'New Arrivals',
])

<div {{ $attributes->merge(["class" => "bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden"]) }}
     x-data="naCarousel()"
     x-init="init()"
     @mouseenter="paused = true; hovering = true"
     @mouseleave="paused = false; hovering = false">

    {{-- ─── Header ─── --}}
    <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-50">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </span>
            <span class="text-sm font-bold text-slate-800">{{ $heading }}</span>
        </div>
        <a href="{{ route($viewAllRoute) }}" class="text-xs text-brand hover:underline font-medium">
            {{ __('ui.view_all') }} &rarr;
        </a>
    </div>

    {{-- ─── Carousel ─── --}}
    <div class="relative">

        {{-- Prev button --}}
        <button @click="prev()"
                x-show="hovering"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute left-1.5 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full
                       bg-white/80 border border-slate-200/70 shadow-sm
                       flex items-center justify-center text-slate-500
                       hover:bg-white hover:text-slate-800 transition-colors"
                aria-label="Previous">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Track wrapper --}}
        <div class="overflow-hidden px-4 py-3">
            <div x-ref="track"
                 class="flex gap-2"
                 :style="`transform: translateX(${offset}px); will-change: transform;`">
                {{--
                  Items are duplicated ×2 so the loop is seamless.
                  When offset reaches -halfWidth we snap back by halfWidth.
                --}}
                @php $items = $products->values(); @endphp
                @foreach([0,1] as $_dup)
                    @foreach($items as $product)
                        @php
                            if ($priceMap !== null) {
                                $displayPrice = $priceMap[$product->id] ?? null;
                                $priceStr     = $displayPrice ? \App\Services\CurrencyService::format($displayPrice) : null;
                            } else {
                                $displayPrice = $product->min_price ?? null;
                                $priceStr     = $displayPrice ? \App\Services\CurrencyService::format($displayPrice) : null;
                            }
                            $name = (app()->getLocale() === 'zh_CN' && $product->name_zh)
                                        ? $product->name_zh
                                        : $product->name_en;
                        @endphp
                        <a href="{{ route($productRoute, $product) }}"
                           class="w-[110px] shrink-0 group"
                           draggable="false">
                            <div class="w-[110px] h-[110px] rounded-lg overflow-hidden bg-slate-50 relative">
                                @if($product->primaryImage())
                                    <img src="{{ $product->primaryImage()->display_url }}"
                                         alt="{{ $name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                         draggable="false"/>
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-7 h-7 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute top-1 left-1 bg-emerald-500 text-white font-bold px-1 py-0.5 rounded uppercase"
                                      style="font-size:8px;">New</span>
                            </div>
                            <p class="text-[11px] text-slate-700 font-medium line-clamp-2 leading-snug mt-1.5 select-none">
                                {{ $name }}
                            </p>
                            @if($priceStr)
                                <p class="text-[12px] font-bold text-brand mt-0.5 tabular-nums">{{ $priceStr }}</p>
                            @elseif($priceMap !== null && !isset($priceMap[$product->id]))
                                <p class="text-[10px] text-slate-400 italic mt-0.5">{{ __('ui.contact_for_price') }}</p>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Next button --}}
        <button @click="next()"
                x-show="hovering"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full
                       bg-white/80 border border-slate-200/70 shadow-sm
                       flex items-center justify-center text-slate-500
                       hover:bg-white hover:text-slate-800 transition-colors"
                aria-label="Next">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>

@once
<script>
function naCarousel() {
    return {
        offset  : 0,
        paused  : false,
        hovering: false,
        _hw     : 0,       // half the track scrollWidth (width of one copy)
        _speed  : 0.055,   // px per ms  ≈ 55px/s at 60fps
        _cw     : 118,     // card width (110) + gap (8)

        init() {
            this.$nextTick(() => {
                this._hw = this.$refs.track.scrollWidth / 2;
                const tick = (ts) => {
                    if (!this.paused && this._hw > 0) {
                        if (this._last !== undefined) {
                            this.offset -= this._speed * (ts - this._last);
                            if (this.offset <= -this._hw) this.offset += this._hw;
                        }
                        this._last = ts;
                    } else {
                        this._last = undefined;
                    }
                    requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        },

        prev() {
            this.offset += this._cw;
            if (this.offset > 0) this.offset -= this._hw;
        },

        next() {
            this.offset -= this._cw;
            if (this.offset <= -this._hw) this.offset += this._hw;
        }
    };
}
</script>
@endonce
