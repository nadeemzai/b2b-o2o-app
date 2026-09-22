<div class="max-w-5xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>

        {{-- Status filter --}}
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="preparing">Preparing</option>
            <option value="ready_for_delivery">Ready for Delivery</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading class="mb-4 text-sm text-blue-600 flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        Loading orders…
    </div>

    @if ($orders->isEmpty())
        {{-- Empty state --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 text-lg font-medium mb-1">No orders yet</p>
            <p class="text-gray-400 text-sm mb-6">
                @if ($statusFilter)
                    No {{ $this->statusLabel($statusFilter) }} orders found.
                @else
                    Your order history will appear here after your first purchase.
                @endif
            </p>
            @if ($statusFilter)
                <button wire:click="$set('statusFilter', '')"
                        class="text-blue-600 text-sm font-medium hover:underline">
                    Show all orders
                </button>
            @else
                <a href="{{ route('retailer.catalogue') }}"
                   class="inline-block bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-blue-800 transition">
                    Browse Catalogue
                </a>
            @endif
        </div>
    @else
        {{-- Orders list --}}
        <div class="space-y-4">
            @foreach ($orders as $order)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                     x-data="{ open: false }">

                    {{-- Order header row --}}
                    <button @click="open = !open"
                            class="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition text-left">

                        <div class="flex items-center gap-4 min-w-0">
                            {{-- Order number --}}
                            <div>
                                <p class="font-mono text-sm font-semibold text-gray-900">
                                    #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $order->created_at->format('d M Y, g:i A') }}
                                </p>
                            </div>

                            {{-- Status badge --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                         {{ $this->statusColor($order->status) }}">
                                {{ $this->statusLabel($order->status) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-6 shrink-0 ml-4">
                            {{-- Item count --}}
                            <div class="text-right hidden sm:block">
                                <p class="text-xs text-gray-400">Items</p>
                                <p class="text-sm font-medium text-gray-700">{{ $order->items->count() }}</p>
                            </div>

                            {{-- Total --}}
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Total</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    PKR {{ number_format($order->total_pkr, 2) }}
                                </p>
                            </div>

                            {{-- Expand arrow --}}
                            <svg :class="open ? 'rotate-180' : ''"
                                 class="h-5 w-5 text-gray-400 transition-transform shrink-0"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    {{-- Expandable items table --}}
                    <div x-show="open" x-collapse class="border-t border-gray-100">
                        <div class="px-5 py-4">

                            {{-- Order notes --}}
                            @if ($order->notes)
                                <p class="text-xs text-gray-500 mb-3 italic">
                                    Note: {{ $order->notes }}
                                </p>
                            @endif

                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs text-gray-400 uppercase tracking-wide">
                                        <th class="text-left pb-2 font-semibold">Product</th>
                                        <th class="text-right pb-2 font-semibold">Qty</th>
                                        <th class="text-right pb-2 font-semibold">Unit Price</th>
                                        <th class="text-right pb-2 font-semibold">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="py-2 text-gray-800 font-medium">
                                                {{ $item->product?->name ?? 'Product #'.$item->product_id }}
                                                @if ($item->product?->sku)
                                                    <span class="text-xs text-gray-400 ml-1">
                                                        {{ $item->product->sku }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-right text-gray-600 font-mono">
                                                {{ $item->qty }}
                                            </td>
                                            <td class="py-2 text-right text-gray-600 font-mono">
                                                PKR {{ number_format($item->unit_price_pkr, 2) }}
                                            </td>
                                            <td class="py-2 text-right font-semibold text-gray-800 font-mono">
                                                PKR {{ number_format($item->line_total_pkr, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t border-gray-200">
                                    <tr>
                                        <td colspan="3" class="pt-3 text-right text-sm font-semibold text-gray-600 pr-4">
                                            Order Total
                                        </td>
                                        <td class="pt-3 text-right font-bold text-blue-700 font-mono">
                                            PKR {{ number_format($order->total_pkr, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Delivery address --}}
                            @if ($order->delivery_address)
                                <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
                                    <span class="font-semibold text-gray-600">Delivery address:</span>
                                    {{ $order->delivery_address }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
