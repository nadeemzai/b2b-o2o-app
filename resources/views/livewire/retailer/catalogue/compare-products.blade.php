{{-- ── Compare Products ──────────────────────────────────────────────────── --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-6 pb-24">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white border-b border-slate-100 shadow-sm px-4 sm:px-6 lg:px-8 py-4 mb-6">
        <div class="max-w-screen-xl mx-auto flex items-center gap-4">
            <a href="{{ route('retailer.catalogue') }}"
               class="flex items-center gap-1.5 text-slate-400 hover:text-brand transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Catalogue
            </a>
            <div class="flex-1">
                <h1 class="text-lg font-bold text-slate-800">Product Comparison</h1>
                <p class="text-xs text-slate-400 mt-0.5">Compare up to 4 products side-by-side</p>
            </div>
            {{-- Clear compare button (Alpine-driven) --}}
            <button
                x-data
                @click="$store.compare.clear(); window.location.href='{{ route('retailer.catalogue') }}'"
                class="text-xs text-slate-500 hover:text-red-500 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear all
            </button>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 max-w-screen-xl mx-auto">

        @if($products->isEmpty())
        {{-- ── Empty state ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-700 mb-2">No products to compare</h2>
            <p class="text-slate-400 text-sm mb-6">Add products from the catalogue using the compare button on each card.</p>
            <a href="{{ route('retailer.catalogue') }}"
               class="inline-flex items-center gap-2 bg-brand hover:bg-orange-600 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                Browse Catalogue
            </a>
        </div>

        @elseif($products->count() === 1)
        {{-- ── Only 1 product ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
            <p class="text-slate-500 font-medium mb-2">You need at least 2 products to compare.</p>
            <a href="{{ route('retailer.catalogue') }}"
               class="text-brand text-sm hover:underline">Add more from the catalogue →</a>
        </div>

        @else
        {{-- ── COMPARISON TABLE ──────────────────────────────────────────── --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm bg-white">
            <table class="w-full border-collapse" style="min-width: {{ 220 + ($products->count() * 220) }}px">

                {{-- ── Product header row ──────────────────────────────── --}}
                <thead>
                    <tr>
                        {{-- Label column --}}
                        <th class="w-44 bg-slate-50 border-b border-r border-slate-100 p-4 text-left">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Attribute</span>
                        </th>
                        {{-- Product columns --}}
                        @foreach($products as $product)
                        <th class="border-b border-r border-slate-100 p-4 align-top last:border-r-0 bg-white">
                            <div class="flex flex-col items-center gap-3">
                                {{-- Product image --}}
                                <div class="w-24 h-24 rounded-xl overflow-hidden border border-slate-100 bg-slate-50 shrink-0">
                                    @if($product->display_src)
                                        <img src="{{ $product->display_src }}"
                                             alt="{{ $product->name_en }}"
                                             class="w-full h-full object-cover"/>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-50 to-white">
                                            <svg class="w-8 h-8 text-brand/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- Product name --}}
                                <div class="text-center">
                                    <a href="{{ route('retailer.catalogue.product', $product) }}"
                                       class="text-sm font-semibold text-slate-800 hover:text-brand transition line-clamp-2 leading-snug">
                                        {{ app()->getLocale() === 'zh_CN' && $product->name_zh ? $product->name_zh : $product->name_en }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 mt-0.5 font-mono">{{ $product->sku }}</p>
                                </div>
                                {{-- Remove button --}}
                                <button
                                    wire:click="removeProduct({{ $product->id }})"
                                    x-data
                                    @click="$store.compare.remove({{ $product->id }})"
                                    class="text-[10px] text-slate-400 hover:text-red-500 flex items-center gap-1 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Remove
                                </button>
                            </div>
                        </th>
                        @endforeach

                        {{-- Add more slot (if < 4 products) --}}
                        @if($products->count() < 4)
                        <th class="border-b border-r border-slate-100 p-4 last:border-r-0 bg-slate-50/50">
                            <div class="flex flex-col items-center gap-3 justify-center h-full py-6">
                                <div class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <a href="{{ route('retailer.catalogue') }}"
                                   class="text-xs text-brand hover:underline font-medium">
                                    + Add product
                                </a>
                            </div>
                        </th>
                        @endif
                    </tr>
                </thead>

                {{-- ── Attribute rows ────────────────────────────────────── --}}
                <tbody class="divide-y divide-slate-50">

                    {{-- Price per unit --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Price / Unit
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            @if($product->retailer_price)
                                <span class="text-lg font-black text-brand">
                                    PKR {{ number_format($product->retailer_price, 0) }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">per {{ $product->unit }}</p>
                            @else
                                <span class="text-xs text-slate-400 italic">Contact for price</span>
                            @endif
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- MOQ --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Min. Order (MOQ)
                        </td>
                        @foreach($products as $product)
                        @php $moq = max(1, (int)($product->moq ?? 1)); @endphp
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            <span class="text-base font-bold text-slate-700 tabular-nums">{{ number_format($moq) }}</span>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $product->unit }}</p>
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- Unit type --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Unit Type
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 uppercase tracking-wide">
                                {{ $product->unit }}
                            </span>
                            @if($product->pieces_per_carton > 1)
                            <p class="text-[10px] text-slate-400 mt-1">{{ $product->pieces_per_carton }} pcs/carton</p>
                            @endif
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- Origin / Brand --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Origin / Brand
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            @if($product->origin)
                                <span class="text-sm font-medium text-slate-700">{{ $product->origin }}</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- Weight --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Weight
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            @if($product->weight_g)
                                @php
                                    $wg = (float) $product->weight_g;
                                    $display = $wg >= 1000
                                        ? number_format($wg / 1000, 2) . ' kg'
                                        : number_format($wg, 0) . ' g';
                                @endphp
                                <span class="text-sm font-medium text-slate-700">{{ $display }}</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- Category --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            Category
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            @if($product->category)
                                <span class="text-xs font-medium text-slate-600">
                                    {{ app()->getLocale() === 'zh_CN' && $product->category->name_zh ? $product->category->name_zh : $product->category->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- SKU --}}
                    <tr class="group hover:bg-orange-50/30 transition-colors">
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            SKU
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-4 text-center last:border-r-0">
                            <span class="text-[11px] font-mono text-slate-500">{{ $product->sku }}</span>
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                    {{-- CTA row --}}
                    <tr>
                        <td class="bg-slate-50 border-r border-slate-100 px-4 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            &nbsp;
                        </td>
                        @foreach($products as $product)
                        <td class="border-r border-slate-100 px-4 py-5 text-center last:border-r-0">
                            <a href="{{ route('retailer.catalogue.product', $product) }}"
                               class="inline-flex items-center gap-1.5 bg-brand hover:bg-orange-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Details
                            </a>
                        </td>
                        @endforeach
                        @if($products->count() < 4)<td class="bg-slate-50/50 border-r border-slate-100 last:border-r-0"></td>@endif
                    </tr>

                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>
