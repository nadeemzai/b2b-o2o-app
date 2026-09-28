@php $order = $order; $retailer = $order->retailer; @endphp
<x-emails.orders.layout subject="Order #{{ $order->id }} Delivered — Thank You!">

<h2>Order Delivered — Thank You! 🎉</h2>
<p>Hi <strong>{{ $retailer?->business_name ?? 'Valued Retailer' }}</strong>, your order <strong>#{{ $order->id }}</strong> has been successfully delivered. We hope everything arrived in perfect condition!</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $order->id }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">PKR {{ number_format($order->total_pkr, 2) }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Delivered On</div>
        <div class="stat-value" style="font-size:13px;">{{ now()->format('d M Y') }}</div>
    </div>
</div>

<p>Need to reorder? Log in to the <a href="{{ config('app.url') }}/retailer/orders" style="color:#1e3a5f;">Retailer Portal</a> and tap <strong>Re-order</strong> on this order.</p>
<p>Thank you for choosing OZ Group for your wholesale needs.</p>

</x-emails.orders.layout>
