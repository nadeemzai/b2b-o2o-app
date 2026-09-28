@php $order = $order; $retailer = $order->retailer; @endphp
<x-emails.orders.layout subject="New Order #{{ $order->id }} — Ready for Fulfillment">

<h2>New Order Ready for Fulfillment</h2>
<p>A verified order from OZ Group has been transferred and is ready for processing.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $order->id }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Retailer</div>
        <div class="stat-value" style="font-size:14px;">{{ $retailer?->business_name ?? '—' }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Huashu Value</div>
        <div class="stat-value">PKR {{ number_format($order->total_pkr, 2) }}</div>
    </div>
</div>

<table class="items">
    <thead>
        <tr><th>Product</th><th>Qty</th><th>Huashu Price</th><th>Line Total</th></tr>
    </thead>
    <tbody>
        @foreach ($order->items as $item)
        <tr>
            <td>{{ $item->product?->name_en ?? $item->product_id }}</td>
            <td>{{ $item->qty }} {{ $item->unit }}</td>
            <td>PKR {{ $item->huashu_unit_price_pkr ? number_format($item->huashu_unit_price_pkr, 2) : '—' }}</td>
            <td>PKR {{ number_format($item->line_total_pkr, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>Please log in to the Huashu portal to manage this order. Huashu Ref: <strong>{{ $order->huashu_ref ?? 'TBD' }}</strong></p>
<p style="color:#888;font-size:12px;">Transferred at {{ $order->transferred_to_huashu_at?->format('d M Y, H:i') }}</p>

</x-emails.orders.layout>
