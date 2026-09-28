@php $order = $order; $retailer = $order->retailer; @endphp
<x-emails.orders.layout subject="Payment Verified — Order #{{ $order->id }}">

<h2>Payment Confirmed ✓</h2>
<p>Hi <strong>{{ $retailer?->business_name ?? 'Valued Retailer' }}</strong>, great news! Your payment for order <strong>#{{ $order->id }}</strong> has been verified by our team.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $order->id }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Amount</div>
        <div class="stat-value">PKR {{ number_format($order->total_pkr, 2) }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Status</div>
        <div class="stat-value"><span class="badge badge-verified">Verified</span></div>
    </div>
</div>

<p>Your order will be transferred to Huashu International shortly for fulfillment. You'll receive another notification once that's done.</p>
<p style="color:#888;font-size:12px;">Verified at {{ $order->payment_verified_at?->format('d M Y, H:i') }}</p>

</x-emails.orders.layout>
