<div class="max-w-5xl mx-auto">

    {{-- ── Flash messages ── --}}
    @if (session()->has('proof_uploaded'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Payment proof uploaded. Our team will verify it shortly.
    </div>
    @endif
    @if (session()->has('reorder_success'))
    <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        {{ session('reorder_success') }}
        <a href="{{ route('retailer.cart') }}" class="ml-auto font-semibold underline">View Cart</a>
    </div>
    @endif
    @if (session()->has('reorder_error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ session('reorder_error') }}
    </div>
    @endif

    {{-- ── Page header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('ui.my_orders') }}</h1>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Currency toggle --}}
            <div class="flex items-center bg-gray-100 rounded-lg p-1 gap-0.5">
                @foreach (['PKR' => 'PKR', 'USD' => '$ USD', 'CNY' => '¥ CNY'] as $code => $label)
                <button wire:click="setCurrency('{{ $code }}')"
                        class="px-3 py-1.5 rounded-md text-xs font-semibold transition
                               {{ $displayCurrency === $code ? 'bg-white text-brand shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">{{ __('ui.all_statuses') }}</option>
                <option value="pending">{{ __('ui.status_pending') }}</option>
                <option value="payment_verified">{{ __('ui.status_payment_verified') }}</option>
                <option value="transferred">{{ __('ui.status_processing') }}</option>
                <option value="fulfilling">{{ __('ui.status_on_its_way') }}</option>
                <option value="delivered">{{ __('ui.status_delivered') }}</option>
                <option value="cancelled">{{ __('ui.status_cancelled') }}</option>
            </select>
        </div>
    </div>

    <div wire:loading class="mb-4 text-sm text-brand flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        {{ __('ui.loading') }}
    </div>

    @if ($orders->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-500 text-lg font-medium mb-1">{{ __('ui.no_orders') }}</p>
        <p class="text-gray-400 text-sm mb-6">
            @if ($statusFilter)
                No {{ $this->statusLabel($statusFilter) }} orders found.
            @else
                {{ __('ui.no_orders_hint') }}
            @endif
        </p>
        @if ($statusFilter)
            <button wire:click="$set('statusFilter', '')" class="text-brand text-sm font-medium hover:underline">{{ __('ui.show_all_orders') }}</button>
        @else
            <a href="{{ route('retailer.catalogue') }}"
               class="inline-block bg-brand text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-brand-dark transition">
                {{ __('ui.browse_catalogue_btn') }}
            </a>
        @endif
    </div>
    @else

    {{-- ── Order list ── --}}
    <div class="space-y-4">
        @foreach ($orders as $order)
        @php
            $isCancelled  = $order->status === 'cancelled';
            $steps        = array_keys($this->timelineSteps());
            $currentIndex = $this->timelineIndex($order->status);
            $sym          = $this->currencySymbol($displayCurrency);
            $amt          = $order->convertedTotal($displayCurrency);
            $amtFmt       = $displayCurrency === 'PKR'
                            ? 'PKR ' . number_format($amt, 0)
                            : $sym . ' ' . number_format($amt, 2);
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{ open: false }">

            {{-- Header row (click = expand accordion) --}}
            <div class="flex items-center px-5 py-4 gap-3">
                <button @click="open = !open"
                        class="flex-1 flex items-center gap-4 min-w-0 text-left hover:opacity-80 transition">
                    <div>
                        <p class="font-mono text-sm font-semibold text-gray-900">
                            #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $this->statusColor($order->status) }}">
                        {{ $this->statusLabel($order->status) }}
                    </span>
                    @if ($order->status === 'pending' && ! $order->payment_proof_path)
                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 border border-amber-200 text-amber-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('ui.upload_payment_proof_btn') }}
                    </span>
                    @elseif ($order->payment_proof_path && $order->status === 'pending')
                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 border border-green-200 text-green-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('ui.proof_submitted_label') }}
                    </span>
                    @endif
                </button>

                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-gray-400">{{ __('ui.items') }}</p>
                        <p class="text-sm font-medium text-gray-700">{{ $order->items->count() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">{{ __('ui.total') }}</p>
                        <p class="text-sm font-semibold text-gray-900 font-mono tabular-nums">{{ $amtFmt }}</p>
                    </div>
                    {{-- View full details button --}}
                    <button wire:click="openDetail({{ $order->id }})"
                            title="Full order details"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-brand hover:bg-brand/5 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </button>
                    <svg @click="open = !open" :class="open ? 'rotate-180' : ''"
                         class="h-5 w-5 text-gray-400 transition-transform shrink-0 cursor-pointer"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Expandable quick-summary body --}}
            <div x-show="open" x-collapse class="border-t border-gray-100">
                <div class="px-5 py-4 space-y-5">

                    {{-- Status timeline --}}
                    @if (! $isCancelled)
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            @foreach ($this->timelineSteps() as $stepKey => $stepLabel)
                            @php $stepIndex = array_search($stepKey, $steps); @endphp
                            <div class="flex flex-col items-center flex-1 relative">
                                @if (! $loop->first)
                                <div class="absolute top-3 right-1/2 w-full h-0.5 -translate-y-1/2
                                    {{ $stepIndex <= $currentIndex ? 'bg-brand' : 'bg-gray-200' }}"></div>
                                @endif
                                <div class="relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center text-white text-xs
                                    {{ $stepIndex < $currentIndex  ? 'bg-brand border-brand' :
                                       ($stepIndex === $currentIndex ? 'bg-brand border-brand ring-4 ring-brand/20' : 'bg-white border-gray-300') }}">
                                    @if ($stepIndex < $currentIndex)
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($stepIndex === $currentIndex)
                                    <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-center text-xs leading-tight
                                    {{ $stepIndex <= $currentIndex ? 'text-brand font-semibold' : 'text-gray-400' }}"
                                   style="max-width:70px">{{ $stepLabel }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-sm text-red-700">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ __('ui.this_order_cancelled') }}
                    </div>
                    @endif

                    {{-- Quick items summary --}}
                    <div class="text-xs text-gray-500">
                        {{ $order->items->count() }} item(s) &mdash;
                        @foreach ($order->items->take(3) as $item)
                        {{ $item->product?->name_en ?? 'Product #'.$item->product_id }} (×{{ $item->qty }}){{ ! $loop->last ? ', ' : '' }}
                        @endforeach
                        @if ($order->items->count() > 3)
                        &hellip; and {{ $order->items->count() - 3 }} more
                        @endif
                    </div>

                    {{-- Link to full details --}}
                    <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                        @if (in_array($order->status, ['delivered', 'cancelled']))
                        <button wire:click="reorder({{ $order->id }})"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-1.5 text-xs font-semibold text-brand border border-brand/30
                                       px-3 py-1.5 rounded-lg hover:bg-brand hover:text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span wire:loading.remove wire:target="reorder({{ $order->id }})">{{ __('ui.reorder') }}</span>
                            <span wire:loading wire:target="reorder({{ $order->id }})">{{ __('ui.adding') }}</span>
                        </button>
                        @else
                        <div></div>
                        @endif
                        <button wire:click="openDetail({{ $order->id }})"
                                class="flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
                            View Full Details
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

    @endif

    {{-- ════════════════════════════════════════════
         Slide-over drawer — comprehensive order detail
         ════════════════════════════════════════════ --}}
    @if ($detailOrder)
    @php
        $dOrder       = $detailOrder;
        $dSteps       = array_keys($this->timelineSteps());
        $dCurrIdx     = $this->timelineIndex($dOrder->status);
        $dIsCancelled = $dOrder->status === 'cancelled';
        $dSym         = $this->currencySymbol($displayCurrency);
        $dFxRate      = $dOrder->fxRate($displayCurrency);
        $dFmt = fn(float $pkr): string => $displayCurrency === 'PKR'
            ? 'PKR ' . number_format($pkr, 0)
            : $dSym . ' ' . number_format(round($pkr * $dFxRate, 2), 2);
    @endphp
    {{-- Backdrop --}}
    <div class="fixed inset-0 z-40 flex justify-end"
         x-data x-init="document.body.style.overflow='hidden'"
         x-on:keydown.escape.window="$wire.closeDetail()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
             wire:click="closeDetail"></div>

        {{-- Panel --}}
        <div class="relative z-50 w-full max-w-2xl bg-white shadow-2xl flex flex-col h-full overflow-hidden">

            {{-- Sticky header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4 shrink-0">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-gray-900 font-mono">
                        Order #{{ str_pad($dOrder->id, 6, '0', STR_PAD_LEFT) }}
                    </h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $this->statusColor($dOrder->status) }}">
                            {{ $this->statusLabel($dOrder->status) }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $dOrder->created_at->format('d M Y, g:i A') }}</span>
                    </div>
                </div>
                {{-- Currency toggle inside drawer --}}
                <div class="flex items-center bg-gray-100 rounded-lg p-0.5 gap-0.5 shrink-0">
                    @foreach (['PKR' => 'PKR', 'USD' => '$', 'CNY' => '¥'] as $code => $label)
                    <button wire:click="setCurrency('{{ $code }}')"
                            class="px-2.5 py-1 rounded-md text-xs font-semibold transition
                                   {{ $displayCurrency === $code ? 'bg-white text-brand shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                <button wire:click="closeDetail"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                {{-- ── Status timeline ── --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Order Progress</h3>
                    @if (! $dIsCancelled)
                    <div class="relative">
                        <div class="flex items-start justify-between">
                            @foreach ($this->timelineSteps() as $stepKey => $stepLabel)
                            @php $stepIdx = array_search($stepKey, $dSteps); @endphp
                            <div class="flex flex-col items-center flex-1 relative">
                                @if (! $loop->first)
                                <div class="absolute top-3 right-1/2 w-full h-0.5 -translate-y-1/2
                                    {{ $stepIdx <= $dCurrIdx ? 'bg-brand' : 'bg-gray-200' }}"></div>
                                @endif
                                <div class="relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center
                                    {{ $stepIdx < $dCurrIdx  ? 'bg-brand border-brand' :
                                       ($stepIdx === $dCurrIdx ? 'bg-brand border-brand ring-4 ring-brand/20' : 'bg-white border-gray-300') }}">
                                    @if ($stepIdx < $dCurrIdx)
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($stepIdx === $dCurrIdx)
                                    <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-center text-xs leading-tight
                                    {{ $stepIdx <= $dCurrIdx ? 'text-brand font-semibold' : 'text-gray-400' }}"
                                   style="max-width:72px">{{ $stepLabel }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        This order was cancelled.
                    </div>
                    @endif
                </div>

                {{-- ── Items table ── --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Items Ordered</h3>
                    <div class="rounded-xl border border-gray-100 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-400 uppercase tracking-wide">
                                    <th class="text-left px-4 py-2.5 font-semibold">Product</th>
                                    <th class="text-right px-4 py-2.5 font-semibold">Qty</th>
                                    <th class="text-right px-4 py-2.5 font-semibold">Unit Price</th>
                                    <th class="text-right px-4 py-2.5 font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($dOrder->items as $item)
                                @php
                                    $unitPkr  = (float) $item->unit_price_pkr;
                                    $linePkr  = (float) ($item->line_total_pkr ?? $unitPkr * $item->qty);
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3 text-gray-800 font-medium">
                                        {{ $item->product?->name_en ?? 'Product #'.$item->product_id }}
                                        @if ($item->variant_label)
                                        <span class="text-xs text-gray-400 block">{{ $item->variant_label }}</span>
                                        @endif
                                        @if ($item->product?->sku)
                                        <span class="text-xs text-gray-400">{{ $item->product->sku }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 font-mono tabular-nums">{{ $item->qty }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 font-mono tabular-nums">{{ $dFmt($unitPkr) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 font-mono tabular-nums">{{ $dFmt($linePkr) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50/60">
                                <tr>
                                    <td colspan="3" class="px-4 pt-3 pb-3 text-right text-sm font-semibold text-gray-600">Order Total</td>
                                    <td class="px-4 pt-3 pb-3 text-right font-bold text-brand font-mono tabular-nums text-base">
                                        {{ $dFmt((float) $dOrder->total_pkr) }}
                                    </td>
                                </tr>
                                @if ($displayCurrency !== 'PKR')
                                <tr>
                                    <td colspan="4" class="px-4 pb-2 text-right text-xs text-gray-400">
                                        = PKR {{ number_format($dOrder->total_pkr, 0) }}
                                        @if ($dOrder->fx_captured_at)
                                        &nbsp;&middot;&nbsp; rate at order date
                                        @else
                                        &nbsp;&middot;&nbsp; live rate (no snapshot)
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ── Payment section (pending orders) ── --}}
                @if ($dOrder->status === \App\Models\Order::STATUS_PENDING)
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Payment</h3>
                    <div class="border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50">
                        @if ($uploadingFor === $dOrder->id)
                        {{-- Upload form --}}
                        <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('ui.upload_payment_screenshot') }}</p>
                        <div class="space-y-3">
                            <input type="file" wire:model="proofFile" accept="image/*"
                                   class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border-0 file:text-xs file:font-semibold
                                          file:bg-brand file:text-white hover:file:bg-brand-dark cursor-pointer" />
                            @error('proofFile')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @if ($proofFile)
                            <img src="{{ $proofFile->temporaryUrl() }}" alt="Preview"
                                 class="h-32 rounded-lg border border-gray-200 object-cover" />
                            @endif
                            <div class="flex gap-2">
                                <button wire:click="uploadProof({{ $dOrder->id }})"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-60"
                                        class="bg-brand text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brand-dark transition">
                                    <span wire:loading.remove wire:target="uploadProof({{ $dOrder->id }})">{{ __('ui.submit_proof') }}</span>
                                    <span wire:loading wire:target="uploadProof({{ $dOrder->id }})">{{ __('ui.uploading') }}</span>
                                </button>
                                <button wire:click="cancelUpload" class="text-xs text-gray-500 hover:text-gray-700 px-3 py-2">{{ __('ui.cancel') }}</button>
                            </div>
                        </div>
                        @elseif ($dOrder->payment_proof_path)
                        {{-- Proof submitted --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('ui.proof_submitted') }}
                                </p>
                                <a href="{{ Storage::disk('public')->url($dOrder->payment_proof_path) }}"
                                   target="_blank"
                                   class="text-xs text-brand underline">{{ __('ui.view_proof') }}</a>
                            </div>
                            <button wire:click="openUpload({{ $dOrder->id }})"
                                    class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5">
                                {{ __('ui.replace') }}
                            </button>
                        </div>
                        @else
                        {{-- Upload prompt with bank details --}}
                        <div class="space-y-3">
                            @php
                                $bankName   = config('payment.bank_name');
                                $acctTitle  = config('payment.account_title');
                                $acctNumber = config('payment.account_number');
                                $iban       = config('payment.iban');
                                $branchCode = config('payment.branch_code');
                                $branchName = config('payment.branch_name');
                            @endphp
                            <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3">
                                <p class="text-xs font-bold text-blue-800 uppercase tracking-wide mb-2">{{ __('ui.payment_bank_details') }}</p>
                                <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                    @if($bankName)<dt class="text-blue-600 font-medium">{{ __('ui.payment_bank_name') }}</dt><dd class="text-blue-900 font-semibold">{{ $bankName }}</dd>@endif
                                    @if($acctTitle)<dt class="text-blue-600 font-medium">{{ __('ui.payment_account_title') }}</dt><dd class="text-blue-900 font-semibold">{{ $acctTitle }}</dd>@endif
                                    @if($acctNumber)<dt class="text-blue-600 font-medium">{{ __('ui.payment_account_number') }}</dt><dd class="text-blue-900 font-semibold font-mono">{{ $acctNumber }}</dd>@endif
                                    @if($iban)<dt class="text-blue-600 font-medium">{{ __('ui.payment_iban') }}</dt><dd class="text-blue-900 font-semibold font-mono">{{ $iban }}</dd>@endif
                                    @if($branchCode)<dt class="text-blue-600 font-medium">{{ __('ui.payment_branch_code') }}</dt><dd class="text-blue-900 font-semibold">{{ $branchCode }}</dd>@endif
                                    @if($branchName)<dt class="text-blue-600 font-medium">{{ __('ui.payment_branch_name') }}</dt><dd class="text-blue-900 font-semibold">{{ $branchName }}</dd>@endif
                                </dl>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500">{{ __('ui.payment_via_bank') }}</p>
                                <button wire:click="openUpload({{ $dOrder->id }})"
                                        class="bg-brand text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brand-dark transition">
                                    {{ __('ui.upload_payment_proof_btn') }}
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif ($dOrder->status !== \App\Models\Order::STATUS_PENDING && $dOrder->payment_proof_path)
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Payment Proof</h3>
                    <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm text-green-800 font-medium">Payment verified by OZ team</span>
                        <a href="{{ Storage::disk('public')->url($dOrder->payment_proof_path) }}"
                           target="_blank"
                           class="ml-auto text-xs text-brand underline">View receipt</a>
                    </div>
                </div>
                @endif

                {{-- ── FX / Rate info ── --}}
                @if ($dOrder->fx_captured_at)
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Exchange Rates at Order Date</h3>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="flex flex-wrap gap-6 text-sm">
                            <div>
                                <span class="text-xs text-gray-400 block mb-0.5">1 USD</span>
                                <span class="font-semibold text-gray-800">
                                    = PKR {{ $dOrder->fx_usd_rate > 0 ? number_format(1 / $dOrder->fx_usd_rate, 0) : '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 block mb-0.5">1 CNY</span>
                                <span class="font-semibold text-gray-800">
                                    = PKR {{ $dOrder->fx_cny_rate > 0 ? number_format(1 / $dOrder->fx_cny_rate, 0) : '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 block mb-0.5">Captured at</span>
                                <span class="font-semibold text-gray-800">{{ $dOrder->fx_captured_at->format('d M Y, g:i A') }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            These rates are locked to the order date for consistent reference. Live rates may differ.
                        </p>
                    </div>
                </div>
                @endif

                {{-- ── Notes ── --}}
                @if ($dOrder->notes)
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</h3>
                    <p class="text-sm text-gray-600 italic bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">{{ $dOrder->notes }}</p>
                </div>
                @endif

                {{-- ── Order metadata ── --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Order Info</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs">
                        <dt class="text-gray-400">Order ID</dt>
                        <dd class="font-mono font-semibold text-gray-700">#{{ str_pad($dOrder->id, 6, '0', STR_PAD_LEFT) }}</dd>
                        <dt class="text-gray-400">Placed</dt>
                        <dd class="text-gray-700">{{ $dOrder->created_at->format('d M Y, g:i A') }}</dd>
                        @if ($dOrder->payment_verified_at)
                        <dt class="text-gray-400">Payment verified</dt>
                        <dd class="text-gray-700">{{ $dOrder->payment_verified_at->format('d M Y, g:i A') }}</dd>
                        @endif
                        @if ($dOrder->transferred_to_huashu_at)
                        <dt class="text-gray-400">Transferred to Huashu</dt>
                        <dd class="text-gray-700">{{ $dOrder->transferred_to_huashu_at->format('d M Y, g:i A') }}</dd>
                        @endif
                        @if ($dOrder->huashu_ref)
                        <dt class="text-gray-400">Huashu Ref</dt>
                        <dd class="font-mono text-gray-700">{{ $dOrder->huashu_ref }}</dd>
                        @endif
                        @if ($dOrder->payment_currency && $dOrder->payment_currency !== 'PKR')
                        <dt class="text-gray-400">Paid in</dt>
                        <dd class="font-semibold text-gray-700">{{ $dOrder->payment_currency }}</dd>
                        @endif
                    </dl>
                </div>

                {{-- ── Reorder button ── --}}
                @if (in_array($dOrder->status, ['delivered', 'cancelled']))
                <div class="pt-2 border-t border-gray-100">
                    <button wire:click="reorder({{ $dOrder->id }})"
                            wire:loading.attr="disabled" wire:loading.class="opacity-60"
                            class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-brand border border-brand/30
                                   px-4 py-2.5 rounded-xl hover:bg-brand hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span wire:loading.remove wire:target="reorder({{ $dOrder->id }})">{{ __('ui.reorder') }}</span>
                        <span wire:loading wire:target="reorder({{ $dOrder->id }})">{{ __('ui.adding') }}</span>
                    </button>
                </div>
                @endif

            </div>{{-- /scrollable body --}}
        </div>{{-- /panel --}}
    </div>{{-- /backdrop+panel wrapper --}}
    @endif

</div>
