<div>
    {{-- ── Header ── --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Orders</h1>
    </div>

    {{-- ── Filters ── --}}
    <div class="flex gap-3 mb-4">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search order # or retailer…"
            class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none"
        />
        <select
            wire:model.live="statusFilter"
            class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none"
        >
            <option value="">All Statuses</option>
            <option value="transferred">Transferred</option>
            <option value="fulfilling">Fulfilling</option>
            <option value="delivered">Delivered</option>
        </select>
    </div>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Order #</th>
                    <th class="px-4 py-3 text-left">Retailer</th>
                    <th class="px-4 py-3 text-left">Transferred At</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Huashu Ref</th>
                    <th class="px-4 py-3 text-right">Total (PKR)</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-semibold text-slate-700">#{{ $order->id }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $order->retailer?->business_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $order->transferred_to_huashu_at?->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $colors = [
                                    'transferred' => 'bg-blue-100 text-blue-700',
                                    'fulfilling'  => 'bg-amber-100 text-amber-700',
                                    'delivered'   => 'bg-emerald-100 text-emerald-700',
                                ];
                                $labels = [
                                    'transferred' => 'Transferred',
                                    'fulfilling'  => 'Fulfilling',
                                    'delivered'   => 'Delivered',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $colors[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $labels[$order->status] ?? ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                            {{ $order->huashu_ref ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-700">
                            {{ number_format($order->total_pkr, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('huashu.orders.show', $order) }}"
                               class="text-brand font-medium hover:underline text-xs">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            No orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination ── --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
