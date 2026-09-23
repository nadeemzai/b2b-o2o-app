@php
    $order = $order;
    $retailer = $order->retailer;
@endphp
<x-emails.orders.layout subject="Order #{{ $order->id }} Confirmed">

<h2>Your Order Has Been Placed!</h2>
<p>Hi <strong>{{ $retailer?->business_name ?? 'Valued Retailer' }}</strong>, we've received your order and it's now pending payment verification.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $order->id }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value">PKR {{ number_format($order->total_pkr, 2) }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Status</div>
        <div class="stat-value"><span class="badge badge-pending">Pending</span></div>
    </div>
</div>

<p><strong>Next step:</strong> Please upload your payment proof through the retailer portal so our team can verify your payment.</p>

<table class="items">
    <thead>
        <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
    </thead>
    <tbody>
        @foreach ($order->items as $item)
        <tr>
            <td>{{ $item->product?->name_en ?? $item->product_id }}</td>
            <td>{{ $item->qty }} {{ $item->unit }}</td>
            <td>PKR {{ number_format($item->unit_price_pkr, 2) }}</td>
            <td>PKR {{ number_format($item->line_total_pkr, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p style="color:#888;font-size:12px;">Order placed on {{ $order->created_at->format('d M Y, H:i') }}</p>

</x-emails.orders.layout>
