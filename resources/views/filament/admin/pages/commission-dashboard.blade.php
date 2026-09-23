<x-filament-panels::page>

    {{-- Date Range Filter --}}
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <form wire:submit="applyFilter">
            {{ $this->form }}
            <div class="flex gap-3 mt-4">
                <x-filament::button type="submit" size="sm">
                    Apply Filter
                </x-filament::button>
                <x-filament::button type="button" wire:click="resetFilter" color="gray" size="sm">
                    Reset to This Month
                </x-filament::button>
            </div>
        </form>
    </div>

    {{-- Stat Tiles --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Total OZ Commission</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $this->formatMoney($stats['total_commission']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Transferred + Fulfilling + Delivered</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Delivered Commission</p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $this->formatMoney($stats['delivered_commission']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Fully completed orders only</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Total Order Value</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $this->formatMoney($stats['total_order_value']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Gross GMV transferred to Huashu</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Pending Verification</p>
            <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ number_format($stats['pending_verification']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Orders awaiting payment check</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Transferred Orders</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($stats['transferred_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Sent to Huashu for fulfillment</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Delivered Orders</p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($stats['delivered_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Successfully completed</p>
        </div>

    </div>

    {{-- Orders Table --}}
    @php $orders = $this->orders; @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                Transferred Orders (Commission Breakdown)
            </h2>
            <span class="text-sm text-gray-500">
                {{ $orders->total() }} orders · {{ $this->formatMoney($stats['total_commission']) }} commission
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Order #</th>
                        <th class="px-4 py-3 text-left">Retailer</th>
                        <th class="px-4 py-3 text-left">Store</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Order Total (PKR)</th>
                        <th class="px-4 py-3 text-right">OZ Commission (PKR)</th>
                        <th class="px-4 py-3 text-left">Transferred At</th>
                        <th class="px-4 py-3 text-left">Verified By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-gray-800 dark:text-gray-100">
                                <a href="{{ route('filament.admin.resources.orders.view', $order) }}"
                                   class="text-primary-600 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $order->retailer?->business_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $order->store?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $order->status === 'delivered'   ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $order->status === 'transferred' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'   : '' }}
                                    {{ $order->status === 'fulfilling'  ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300'   : '' }}
                                ">
                                    {{ $this->statusLabel($order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700 dark:text-gray-300">
                                {{ number_format($order->total_pkr, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-primary-600 dark:text-primary-400">
                                {{ $order->oz_commission_pkr ? number_format($order->oz_commission_pkr, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $order->transferred_to_huashu_at?->format('d M Y, H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $order->paymentVerifiedBy?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                No transferred orders found for the selected date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($orders->count() > 0)
                <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Page Totals</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-gray-700 dark:text-gray-300">
                            {{ number_format($orders->sum('total_pkr'), 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($orders->sum('oz_commission_pkr'), 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orders->lastPage() > 1)
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}
            </p>
            <div class="flex gap-2">
                <x-filament::button
                    wire:click="previousPage"
                    color="gray"
                    size="sm"
                    :disabled="$page <= 1"
                >
                    ← Previous
                </x-filament::button>
                <span class="flex items-center text-sm text-gray-500 px-2">
                    Page {{ $page }} of {{ $orders->lastPage() }}
                </span>
                <x-filament::button
                    wire:click="nextPage"
                    color="gray"
                    size="sm"
                    :disabled="$page >= $orders->lastPage()"
                >
                    Next →
                </x-filament::button>
            </div>
        </div>
        @endif
    </div>

</x-filament-panels::page>
