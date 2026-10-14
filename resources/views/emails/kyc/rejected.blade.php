@php
    $retailer = $retailer;
@endphp
<x-emails.orders.layout subject="KYC Application Update — Action Required">

<h2>KYC Application — Action Required</h2>
<p>Hi <strong>{{ $retailer->business_name ?? 'Valued Retailer' }}</strong>, your KYC application has been reviewed and unfortunately could not be approved at this time.</p>

<div class="stat-row">
    <div class="stat">
        <div class="stat-label">Account Status</div>
        <div class="stat-value"><span class="badge badge-pending">Rejected</span></div>
    </div>
    <div class="stat">
        <div class="stat-label">Business Name</div>
        <div class="stat-value" style="font-size:15px;">{{ $retailer->business_name ?? '—' }}</div>
    </div>
</div>

<p><strong>Reason for rejection:</strong></p>
<div style="background:#fff3cd; border-left:4px solid #ffc107; border-radius:4px; padding:12px 16px; margin:0 0 20px; font-size:14px; color:#856404; line-height:1.6;">
    {{ $reason }}
</div>

<p>To resolve this, please log back in to the portal, correct the issues noted above, and resubmit your KYC documents.</p>

<p style="text-align:center; margin: 24px 0;">
    <a href="{{ config('app.url') }}/retailer/kyc/upload" class="btn">Resubmit KYC Documents</a>
</p>

<p style="color:#888;font-size:12px;">If you believe this decision was made in error or need assistance, contact your OZ Group representative directly.</p>

</x-emails.orders.layout>
