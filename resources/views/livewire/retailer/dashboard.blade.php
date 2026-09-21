<div class="space-y-6">

    {{-- Welcome header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Welcome, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $retailer->business_name }} &middot; {{ $retailer->store->name ?? '—' }}</p>
        </div>
        <a href="{{ route('retailer.catalogue') }}"
           class="bg-brand text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 transition">
            Browse Catalogue
        </a>
    </div>

    {{-- Stats tiles --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $tiles = [
                ['label' => 'Pending Orders',   'value' => $stats['pending'],   'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50'],
                ['label' => 'Preparing',         'value' => $stats['preparing'], 'color' => 'text-blue-600',   'bg' => 'bg-blue-50'],
                ['label' => 'Delivered',         'value' => $stats['delivered'], 'color' => 'text-emerald-600','bg' => 'bg-emerald-50'],
                ['label' => 'Total Orders',      'value' => $stats['total'],     'color' => 'text-slate-700',  'bg' => 'bg-slate-100'],
            ];
        @endphp
        @foreach($tiles as $tile)
        <div class="bg-white rounded-xl border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $tile['label'] }}</p>
            <p class="text-3xl font-bold {{ $tile['color'] }} mt-2 tabular-nums">{{ $tile['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Recent Orders</h2>
            <a href="{{ route('retailer.orders') }}" class="text-xs text-brand hover:underline">View all →</a>
        </div>

        @if($recentOrders->isEmpty())
        <div class="px-5 py-10 text-center text-slate-400 text-sm">
            No orders yet. <a href="{{ route('retailer.catalogue') }}" class="text-brand hover:underline">Browse the catalogue</a> to place your first order.
        </div>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($recentOrders as $order)
            <div class="px-5 py-3.5 flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-slate-800">Order #{{ $order->id }}</span>
                    <span class="text-xs text-slate-400 ml-2">{{ $order->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 tabular-nums">
                        PKR {{ number_format($order->total_pkr, 0) }}
                    </span>
                    @php
                        $badge = match($order->status) {
                            'pending'            => 'bg-yellow-100 text-yellow-700',
                            'preparing'          => 'bg-blue-100 text-blue-700',
                            'ready_for_delivery' => 'bg-purple-100 text-purple-700',
                            'delivered'          => 'bg-emerald-100 text-emerald-700',
                            'cancelled'          => 'bg-red-100 text-red-700',
                            default              => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $badge }}">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Quick links --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('retailer.catalogue') }}" class="group bg-white border border-slate-100 rounded-xl p-5 shadow-sm hover:border-brand transition">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="font-semibold text-slate-800 text-sm">Browse Catalogue</p>
            <p class="text-xs text-slate-400 mt-0.5">View available products</p>
        </a>
        <a href="{{ route('retailer.cart') }}" class="group bg-white border border-slate-100 rounded-xl p-5 shadow-sm hover:border-brand transition">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="font-semibold text-slate-800 text-sm">My Cart</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $cartCount }} item(s) waiting</p>
        </a>
        <a href="{{ route('retailer.orders') }}" class="group bg-white border border-slate-100 rounded-xl p-5 shadow-sm hover:border-brand transition">
            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="font-semibold text-slate-800 text-sm">Order History</p>
            <p class="text-xs text-slate-400 mt-0.5">Track all your orders</p>
        </a>
    </div>

</div>
