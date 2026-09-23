<div class="max-w-5xl mx-auto">

    {{-- Flash messages --}}
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

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('ui.my_orders') }}</h1>
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

    <div class="space-y-4">
        @foreach ($orders as $order)
        @php
            $isCancelled  = $order->status === 'cancelled';
            $steps        = array_keys($this->timelineSteps());
            $currentIndex = $this->timelineIndex($order->status);
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{ open: false }">

            {{-- Header row --}}
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition text-left">
                <div class="flex items-center gap-4 min-w-0">
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
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 border border-amber-200 text-amber-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('ui.upload_payment_proof_btn') }}
                    </span>
                    @elseif ($order->payment_proof_path && $order->status === 'pending')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 border border-green-200 text-green-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('ui.proof_submitted_label') }}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-6 shrink-0 ml-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-gray-400">{{ __('ui.items') }}</p>
                        <p class="text-sm font-medium text-gray-700">{{ $order->items->count() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">{{ __('ui.total') }}</p>
                        <p class="text-sm font-semibold text-gray-900">PKR {{ number_format($order->total_pkr, 2) }}</p>
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                         class="h-5 w-5 text-gray-400 transition-transform shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            {{-- Expandable body --}}
            <div x-show="open" x-collapse class="border-t border-gray-100">
                <div class="px-5 py-4 space-y-5">

                    {{-- ── Status timeline ── --}}
                    @if (! $isCancelled)
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            @foreach ($this->timelineSteps() as $stepKey => $stepLabel)
                            @php $stepIndex = array_search($stepKey, $steps); @endphp
                            <div class="flex flex-col items-center flex-1 relative">
                                {{-- Connector line (between dots) --}}
                                @if (! $loop->first)
                                <div class="absolute top-3 right-1/2 w-full h-0.5 -translate-y-1/2
                                    {{ $stepIndex <= $currentIndex ? 'bg-brand' : 'bg-gray-200' }}">
                                </div>
                                @endif
                                {{-- Dot --}}
                                <div class="relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center text-white text-xs
                                    {{ $stepIndex < $currentIndex  ? 'bg-brand border-brand' :
                                       ($stepIndex === $currentIndex ? 'bg-brand border-brand ring-4 ring-brand/20' : 'bg-white border-gray-300') }}">
                                    @if ($stepIndex < $currentIndex)
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($stepIndex === $currentIndex)
                                    <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                {{-- Label --}}
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

                    {{-- ── Payment proof upload (pending only) ── --}}
                    @if ($order->status === \App\Models\Order::STATUS_PENDING)
                    <div class="border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50">
                        @if ($uploadingFor === $order->id)
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
                                <button wire:click="uploadProof({{ $order->id }})"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-60"
                                        class="bg-brand text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brand-dark transition">
                                    <span wire:loading.remove wire:target="uploadProof({{ $order->id }})">{{ __('ui.submit_proof') }}</span>
                                    <span wire:loading wire:target="uploadProof({{ $order->id }})">{{ __('ui.uploading') }}</span>
                                </button>
                                <button wire:click="cancelUpload" class="text-xs text-gray-500 hover:text-gray-700 px-3 py-2">{{ __('ui.cancel') }}</button>
                            </div>
                        </div>
                        @elseif ($order->payment_proof_path)
                        {{-- Proof already submitted --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">{{ __('ui.proof_submitted') }}</p>
                                <a href="{{ Storage::disk('public')->url($order->payment_proof_path) }}"
                                   target="_blank"
                                   class="text-xs text-brand underline">{{ __('ui.view_proof') }}</a>
                            </div>
                            <button wire:click="openUpload({{ $order->id }})"
                                    class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5">
                                {{ __('ui.replace') }}
                            </button>
                        </div>
                        @else
                        {{-- Prompt to upload --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">{{ __('ui.payment_required') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('ui.payment_via_bank') }}</p>
                            </div>
                            <button wire:click="openUpload({{ $order->id }})"
                                    class="bg-brand text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brand-dark transition">
                                {{ __('ui.upload_payment_proof_btn') }}
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- ── Items table ── --}}
                    @if ($order->notes)
                    <p class="text-xs text-gray-500 italic">Note: {{ $order->notes }}</p>
                    @endif

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-400 uppercase tracking-wide">
                                <th class="text-left pb-2 font-semibold">{{ __('ui.product_name') }}</th>
                                <th class="text-right pb-2 font-semibold">{{ __('ui.qty') }}</th>
                                <th class="text-right pb-2 font-semibold">{{ __('ui.unit_price') }}</th>
                                <th class="text-right pb-2 font-semibold">{{ __('ui.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($order->items as $item)
                            <tr>
                                <td class="py-2 text-gray-800 font-medium">
                                    {{ $item->product?->name_en ?? 'Product #'.$item->product_id }}
                                    @if ($item->product?->sku)
                                    <span class="text-xs text-gray-400 ml-1">{{ $item->product->sku }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right text-gray-600 font-mono">{{ $item->qty }}</td>
                                <td class="py-2 text-right text-gray-600 font-mono">PKR {{ number_format($item->unit_price_pkr, 2) }}</td>
                                <td class="py-2 text-right font-semibold text-gray-800 font-mono">PKR {{ number_format($item->line_total_pkr, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="pt-3 text-right text-sm font-semibold text-gray-600 pr-4">{{ __('ui.order_total_label') }}</td>
                                <td class="pt-3 text-right font-bold text-brand font-mono">PKR {{ number_format($order->total_pkr, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    @if ($order->delivery_address)
                    <div class="pt-3 border-t border-gray-100 text-xs text-gray-500">
                        <span class="font-semibold text-gray-600">{{ __('ui.delivery_address_label') }}</span>
                        {{ $order->delivery_address }}
                    </div>
                    @endif

                    {{-- ── Re-order button ── --}}
                    @if (in_array($order->status, ['delivered', 'cancelled']))
                    <div class="flex justify-end pt-2 border-t border-gray-100">
                        <button wire:click="reorder({{ $order->id }})"
                                wire:loading.attr="disabled" wire:loading.class="opacity-60"
                                class="flex items-center gap-1.5 text-xs font-semibold text-brand border border-brand/30
                                       px-4 py-2 rounded-lg hover:bg-brand hover:text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span wire:loading.remove wire:target="reorder({{ $order->id }})">{{ __('ui.reorder') }}</span>
                            <span wire:loading wire:target="reorder({{ $order->id }})">{{ __('ui.adding') }}</span>
                        </button>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

    @endif
</div>
