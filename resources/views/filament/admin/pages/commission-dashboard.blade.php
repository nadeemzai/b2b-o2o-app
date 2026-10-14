<x-filament-panels::page>

    {{-- ── Date Range Filter ──────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form wire:submit="applyFilter">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{ $this->form }}
                </div>
                <div class="flex items-center gap-2 sm:pb-0">
                    <x-filament::button type="submit" size="sm">
                        Apply Filter
                    </x-filament::button>
                    <x-filament::button type="button" wire:click="resetFilter" color="gray" size="sm">
                        Reset to This Month
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Stat Tiles ──────────────────────────────────────── --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total OZ Commission</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 leading-tight">{{ $this->formatMoney($stats['total_commission']) }}</p>
            <p class="text-xs text-gray-400">Transferred + Fulfilling + Delivered</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Delivered Commission</p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400 leading-tight">{{ $this->formatMoney($stats['delivered_commission']) }}</p>
            <p class="text-xs text-gray-400">Fully completed orders only</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Order Value</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ $this->formatMoney($stats['total_order_value']) }}</p>
            <p class="text-xs text-gray-400">Gross GMV transferred to Huashu</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-yellow-300 dark:border-yellow-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending Verification</p>
            <p class="text-2xl font-bold text-warning-600 dark:text-warning-400 leading-tight">{{ number_format($stats['pending_verification']) }}</p>
            <p class="text-xs text-gray-400">Orders awaiting payment check</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Transferred Orders</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ number_format($stats['transferred_count']) }}</p>
            <p class="text-xs text-gray-400">Sent to Huashu for fulfillment</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Delivered Orders</p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400 leading-tight">{{ number_format($stats['delivered_count']) }}</p>
            <p class="text-xs text-gray-400">Successfully completed</p>
        </div>

    </div>

    {{-- ── Orders Table ─────────────────────────────────────── --}}
    @php $orders = $this->orders; @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- Table header row --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                Transferred Orders (Commission Breakdown)
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                {{ $orders->total() }} orders &middot; {{ $this->formatMoney($stats['total_commission']) }} commission
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Order #</th>
                        <th class="px-6 py-3 text-left font-semibold">Retailer</th>
                        <th class="px-6 py-3 text-left font-semibold">Store</th>
                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                        <th class="px-6 py-3 text-right font-semibold">Order Total (PKR)</th>
                        <th class="px-6 py-3 text-right font-semibold">OZ Commission (PKR)</th>
                        <th class="px-6 py-3 text-left font-semibold">Transferred At</th>
                        <th class="px-6 py-3 text-left font-semibold">Verified By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-semibold">
                                <a href="{{ route('filament.admin.resources.orders.view', $order) }}"
                                   class="text-primary-600 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium">
                                {{ $order->retailer?->business_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $order->store?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    {{ $order->status === 'delivered'   ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $order->status === 'transferred' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'   : '' }}
                                    {{ $order->status === 'fulfilling'  ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300'   : '' }}
                                ">
                                    {{ $this->statusLabel($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-gray-700 dark:text-gray-300">
                                {{ number_format($order->total_pkr, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-semibold text-primary-600 dark:text-primary-400">
                                {{ $order->oz_commission_pkr ? number_format($order->oz_commission_pkr, 2) : '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                {{ $order->transferred_to_huashu_at?->format('d M Y, H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $order->paymentVerifiedBy?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                No transferred orders found for the selected date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($orders->count() > 0)
                <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Page Totals</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-gray-700 dark:text-gray-300">
                            {{ number_format($orders->sum('total_pkr'), 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-primary-600 dark:text-primary-400">
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
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </p>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="previousPage" color="gray" size="sm" :disabled="$page <= 1">
                    ← Previous
                </x-filament::button>
                <span class="text-xs text-gray-500 dark:text-gray-400 px-1">
                    {{ $page }} / {{ $orders->lastPage() }}
                </span>
                <x-filament::button wire:click="nextPage" color="gray" size="sm" :disabled="$page >= $orders->lastPage()">
                    Next →
                </x-filament::button>
            </div>
        </div>
        @endif

    </div>

</x-filament-panels::page>
