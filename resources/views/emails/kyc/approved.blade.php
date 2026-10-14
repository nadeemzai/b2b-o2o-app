@php
    $retailer = $retailer;
@endphp
<x-emails.orders.layout subject="Your KYC Has Been Approved">

<h2>🎉 KYC Application Approved!</h2>
<p>Hi <strong>{{ $retailer->business_name ?? 'Valued Retailer' }}</strong>, congratulations — your KYC application has been reviewed and <strong>approved</strong> by the OZ Group admin team.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Account Status</div>
        <div class="stat-value"><span class="badge badge-fulfilling">Approved</span></div>
    </div>
    <div class="stat">
        <div class="stat-label">Business Name</div>
        <div class="stat-value" style="font-size:15px;">{{ $retailer->business_name ?? '—' }}</div>
    </div>
</div>

<p>You can now log in to the OZ Group B2B portal to:</p>
<ul style="margin:0 0 16px; padding-left:20px; font-size:14px; line-height:1.8; color:#444;">
    <li>Browse the full product catalogue</li>
    <li>Place wholesale orders</li>
    <li>Track your order status in real time</li>
</ul>

<p style="text-align:center; margin: 24px 0;">
    <a href="{{ config('app.url') }}/retailer/login" class="btn">Log In to the Portal</a>
</p>

<p style="color:#888;font-size:12px;">If you have any questions, please contact your assigned OZ Group representative.</p>

</x-emails.orders.layout>
