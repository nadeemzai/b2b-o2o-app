<div>
    {{-- ── Flash ── --}}
    @if (session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('huashu.orders') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back to Orders</a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1">Order #{{ $order->id }}</h1>
        </div>

        {{-- Status badge ── --}}
        @php
            $badgeColors = [
                'transferred' => 'bg-blue-100 text-blue-700',
                'fulfilling'  => 'bg-amber-100 text-amber-700',
                'delivered'   => 'bg-emerald-100 text-emerald-700',
            ];
            $badgeLabels = [
                'transferred' => 'Transferred',
                'fulfilling'  => 'Fulfilling',
                'delivered'   => 'Delivered',
            ];
        @endphp
        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badgeColors[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
            {{ $badgeLabels[$order->status] ?? ucfirst($order->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: items + timeline ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Order Items ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-700">Items</div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">Unit Price (PKR)</th>
                            <th class="px-4 py-2 text-right">Line Total (PKR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-slate-700">{{ $item->product?->name_en ?? 'Product #'.$item->product_id }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $item->qty }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format($item->unit_price_pkr, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ number_format($item->line_total_pkr, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-slate-700">Order Total</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800">PKR {{ number_format($order->total_pkr, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>


            {{-- ── Order Progress Bar ── --}}
            @php
                $huashuSteps = ['transferred', 'fulfilling', 'delivered'];
                $huashuLabels = [
                    'transferred' => 'Received',
                    'fulfilling'  => 'Fulfilling',
                    'delivered'   => 'Delivered',
                ];
                $huashuCurrentIndex = array_search($order->status, $huashuSteps, true);
                if ($huashuCurrentIndex === false) { $huashuCurrentIndex = 0; }
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-5">
                <div class="relative">
                    <div class="flex items-center justify-between">
                        @foreach ($huashuSteps as $hIdx => $hStep)
                            <div class="flex flex-col items-center flex-1 relative">
                                @if ($hIdx > 0)
                                    <div class="absolute top-3 right-1/2 w-full h-0.5 -translate-y-1/2 {{ $hIdx <= $huashuCurrentIndex ? 'bg-brand' : 'bg-gray-200' }}"></div>
                                @endif
                                <div class="relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center text-white text-xs
                                    {{ $hIdx < $huashuCurrentIndex
                                        ? 'bg-brand border-brand'
                                        : ($hIdx === $huashuCurrentIndex
                                            ? 'bg-brand border-brand ring-4 ring-brand/20'
                                            : 'bg-white border-gray-300') }}">
                                    @if ($hIdx < $huashuCurrentIndex)
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif ($hIdx === $huashuCurrentIndex)
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-center text-xs leading-tight
                                    {{ $hIdx <= $huashuCurrentIndex ? 'text-brand font-semibold' : 'text-gray-400' }}"
                                   style="max-width:70px">
                                    {{ $huashuLabels[$hStep] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Status History ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-700">History</div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($order->statusHistory->sortByDesc('created_at') as $history)
                        <li class="px-4 py-3 text-sm text-slate-600 flex items-start gap-3">
                            <span class="mt-0.5 w-2 h-2 rounded-full bg-brand flex-shrink-0"></span>
                            <div>
                                <span class="font-medium text-slate-800">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
                                @if ($history->note)
                                    <span class="text-slate-400"> — {{ $history->note }}</span>
                                @endif
                                <div class="text-slate-400 text-xs mt-0.5">
                                    {{ $history->created_at->format('d M Y, H:i') }}
                                    @if($history->changedBy) · {{ $history->changedBy->name }} @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ── Right: actions sidebar ── --}}
        <div class="space-y-4">

            {{-- Retailer Info ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <h3 class="font-semibold text-slate-700 mb-2 text-sm uppercase tracking-wide">Retailer</h3>
                <p class="text-slate-800 font-medium">{{ $order->retailer?->business_name }}</p>
                <p class="text-slate-500 text-sm">Order placed: {{ $order->created_at->format('d M Y') }}</p>
                <p class="text-slate-500 text-sm">Transferred: {{ $order->transferred_to_huashu_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>

            {{-- Huashu Reference ── --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <h3 class="font-semibold text-slate-700 mb-2 text-sm uppercase tracking-wide">Huashu Reference</h3>

                @if ($showRefForm)
                    <form wire:submit.prevent="saveRef" class="space-y-2">
                        <input
                            wire:model="huashuRef"
                            type="text"
                            placeholder="e.g. HS-20260923-001"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none"
                        />
                        @error('huashuRef') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 bg-brand text-white rounded-lg px-3 py-2 text-sm font-medium hover:bg-brand-dark transition">
                                Save
                            </button>
                            <button type="button" wire:click="$set('showRefForm', false)"
                                class="flex-1 border border-slate-300 text-slate-600 rounded-lg px-3 py-2 text-sm hover:bg-slate-50 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-slate-800 font-mono text-sm mb-2">
                        {{ $order->huashu_ref ?? '— not set —' }}
                    </p>
                    @if ($order->status !== 'delivered')
                        <button wire:click="$set('showRefForm', true)"
                            class="text-brand text-sm hover:underline font-medium">
                            {{ $order->huashu_ref ? 'Edit' : 'Set Reference' }} →
                        </button>
                    @endif
                @endif
            </div>

            {{-- Actions ── --}}
            @if ($order->status !== 'delivered')
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 space-y-3">
                    <h3 class="font-semibold text-slate-700 mb-2 text-sm uppercase tracking-wide">Actions</h3>

                    @if ($order->canMarkFulfilling())
                        <button
                            wire:click="markFulfilling"
                            wire:confirm="Mark this order as fulfilling? This confirms Huashu has started processing."
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded-lg px-4 py-2.5 text-sm font-semibold transition">
                            Mark as Fulfilling
                        </button>
                    @endif

                    @if ($order->canMarkDelivered())
                        <button
                            wire:click="markDelivered"
                            wire:confirm="Mark this order as delivered? This is final."
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-4 py-2.5 text-sm font-semibold transition">
                            Mark as Delivered
                        </button>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>
