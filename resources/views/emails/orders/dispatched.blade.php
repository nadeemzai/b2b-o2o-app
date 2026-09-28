@php
    $orderId  = str_pad($order->id, 6, '0', STR_PAD_LEFT);
    $retailer = $order->retailer;
@endphp
<x-emails.orders.layout subject="Order #{{ $orderId }} Dispatched — En Route to OZ Store">

<h2>Your Order Is On Its Way! 🚚</h2>
<p>Hi <strong>{{ $retailer?->business_name ?? 'Valued Retailer' }}</strong>, great news — your order <strong>#{{ $orderId }}</strong> has been dispatched from our Huashu warehouse and is now en route to the OZ township store.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Order #</div>
        <div class="stat-value">#{{ $orderId }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Order Total</div>
        <div class="stat-value" style="font-size:16px;">PKR {{ number_format($order->total_pkr, 0) }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Dispatched On</div>
        <div class="stat-value" style="font-size:13px;">{{ now()->format('d M Y') }}</div>
    </div>
</div>

<p style="background:#fff8e1;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:0 6px 6px 0;font-size:13px;color:#78350f;margin:0 0 16px;">
    <strong>What happens next?</strong> Once the goods arrive at the OZ township store, our team will confirm delivery and you'll receive another email. You can track progress anytime in the Retailer Portal.
</p>

@if($order->items->count())
<table class="items">
    <thead>
        <tr>
            <th>Product</th>
            <th style="text-align:right;">Qty</th>
            <th style="text-align:right;">Unit Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items->take(5) as $item)
        <tr>
            <td>{{ $item->product?->name_en ?? 'Product #'.$item->product_id }}</td>
            <td style="text-align:right;">{{ $item->qty }}</td>
            <td style="text-align:right;">PKR {{ number_format($item->unit_price_pkr, 0) }}</td>
        </tr>
        @endforeach
        @if($order->items->count() > 5)
        <tr>
            <td colspan="3" style="color:#888;font-style:italic;">… and {{ $order->items->count() - 5 }} more item(s)</td>
        </tr>
        @endif
    </tbody>
</table>
@endif

<p style="text-align:center;margin:24px 0 8px;">
    <a href="{{ config('app.url') }}/retailer/orders" class="btn">Track Your Order</a>
</p>
<p>Thank you for choosing OZ Group for your wholesale needs.</p>

</x-emails.orders.layout>
