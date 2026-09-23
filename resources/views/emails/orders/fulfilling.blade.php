@php $order = $order; $retailer = $order->retailer; @endphp
<x-emails.orders.layout subject="Order #{{ $order->id }} Is On Its Way!">

<h2>Your Order Is On Its Way! 🚚</h2>
<p>Hi <strong>{{ $retailer?->business_name ?? 'Valued Retailer' }}</strong>, your order <strong>#{{ $order->id }}</strong> is now being fulfilled by Huashu International and will be delivered soon.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $order->id }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Status</div>
        <div class="stat-value"><span class="badge badge-fulfilling">On Its Way</span></div>
    </div>
    @if($order->huashu_ref)
    <div class="stat">
        <div class="stat-label">Tracking Ref</div>
        <div class="stat-value" style="font-size:14px;">{{ $order->huashu_ref }}</div>
    </div>
    @endif
</div>

<p>Our delivery team will contact you shortly to confirm the delivery time. Please ensure someone is available to receive the goods.</p>
<p>You can track your order status in the <a href="{{ config('app.url') }}/retailer/orders" style="color:#1e3a5f;">Retailer Portal</a>.</p>

</x-emails.orders.layout>
