<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #{{ $order->id }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        background: #fff;
    }

    /* ── Page layout ─────────────────────────────────── */
    .page { padding: 32px 36px; }

    /* ── Header ─────────────────────────────────────── */
    .header {
        background: #1e3a5f;
        color: #fff;
        padding: 18px 24px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: table;
        width: 100%;
    }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .brand { font-size: 15px; font-weight: bold; letter-spacing: .5px; }
    .brand-sub { font-size: 10px; color: #93c5fd; margin-top: 2px; }
    .order-num { font-size: 22px; font-weight: bold; }
    .order-date { font-size: 10px; color: #93c5fd; margin-top: 3px; }

    /* ── Status badge ────────────────────────────────── */
    .status-row {
        margin-bottom: 18px;
        display: table;
        width: 100%;
    }
    .status-cell { display: table-cell; vertical-align: middle; }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .badge-pending          { background: #fef3c7; color: #92400e; }
    .badge-payment_verified { background: #dbeafe; color: #1e40af; }
    .badge-transferred      { background: #e0e7ff; color: #3730a3; }
    .badge-fulfilling       { background: #fef9c3; color: #713f12; }
    .badge-delivered        { background: #dcfce7; color: #14532d; }
    .badge-cancelled        { background: #fee2e2; color: #7f1d1d; }

    /* ── Two-column meta grid ────────────────────────── */
    .meta-grid {
        display: table;
        width: 100%;
        margin-bottom: 18px;
        border-collapse: collapse;
    }
    .meta-col {
        display: table-cell;
        width: 50%;
        padding-right: 12px;
        vertical-align: top;
    }
    .meta-col:last-child { padding-right: 0; }
    .meta-card {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 14px;
    }
    .meta-card h4 {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #64748b;
        margin-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 5px;
    }
    .meta-dl { width: 100%; }
    .meta-dl tr td { padding: 2px 0; }
    .meta-dl .lbl { color: #64748b; width: 48%; font-size: 10px; }
    .meta-dl .val { font-weight: 600; color: #1e293b; font-size: 10px; }

    /* ── Section headings ────────────────────────────── */
    .section-title {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #1e3a5f;
        border-bottom: 2px solid #1e3a5f;
        padding-bottom: 4px;
        margin-bottom: 10px;
    }

    /* ── Items table ─────────────────────────────────── */
    .items-section { margin-bottom: 18px; }
    .items-table {
        width: 100%;
        border-collapse: collapse;
    }
    .items-table thead tr {
        background: #1e3a5f;
        color: #fff;
    }
    .items-table thead th {
        padding: 7px 10px;
        text-align: left;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .items-table thead th.num { text-align: right; }
    .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .items-table tbody tr:nth-child(even) { background: #f8fafc; }
    .items-table tbody td { padding: 7px 10px; font-size: 10px; color: #334155; }
    .items-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
    .items-table tfoot tr { border-top: 2px solid #1e3a5f; }
    .items-table tfoot td { padding: 8px 10px; font-size: 11px; }
    .items-table tfoot .total-lbl { font-weight: bold; color: #1e3a5f; }
    .items-table tfoot .total-val { font-weight: bold; color: #1e3a5f; text-align: right; font-variant-numeric: tabular-nums; }

    /* ── FX box ──────────────────────────────────────── */
    .fx-box {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 18px;
    }
    .fx-box h4 { font-size: 9px; text-transform: uppercase; letter-spacing: .8px; color: #0369a1; margin-bottom: 6px; }
    .fx-row { display: table; width: 100%; }
    .fx-cell { display: table-cell; width: 33.33%; padding-right: 8px; }
    .fx-cell:last-child { padding-right: 0; }
    .fx-currency { font-size: 10px; color: #475569; }
    .fx-amount { font-size: 13px; font-weight: bold; color: #0c4a6e; }
    .fx-rate { font-size: 9px; color: #64748b; margin-top: 2px; }

    /* ── Timeline ────────────────────────────────────── */
    .timeline-section { margin-bottom: 18px; }
    .timeline-table { width: 100%; border-collapse: collapse; }
    .timeline-table tr { border-bottom: 1px solid #f1f5f9; }
    .timeline-table td { padding: 5px 8px; font-size: 10px; }
    .timeline-table .from { color: #64748b; }
    .timeline-table .to   { font-weight: 600; color: #1e293b; }
    .timeline-table .by   { color: #94a3b8; }
    .timeline-table .at   { color: #94a3b8; text-align: right; }

    /* ── Footer ──────────────────────────────────────── */
    .footer {
        margin-top: 24px;
        text-align: center;
        font-size: 9px;
        color: #94a3b8;
        border-top: 1px solid #e2e8f0;
        padding-top: 10px;
    }
</style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-left">
            <div class="brand">OZ Tech B2B Platform</div>
            <div class="brand-sub">Wholesale Marketplace · Pakistan</div>
        </div>
        <div class="header-right">
            <div class="order-num">Order #{{ $order->id }}</div>
            <div class="order-date">Placed {{ $order->created_at->format('d M Y, H:i') }}</div>
        </div>
    </div>

    {{-- ── Status ── --}}
    @php
        $statusLabels = [
            'pending'          => 'Pending',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Transferred to Huashu',
            'fulfilling'       => 'Fulfilling',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
        ];
        $statusLabel = $statusLabels[$order->status] ?? ucwords(str_replace('_', ' ', $order->status));
    @endphp
    <div class="status-row">
        <div class="status-cell">
            <span class="badge badge-{{ $order->status }}">{{ $statusLabel }}</span>
        </div>
    </div>

    {{-- ── Meta grid ── --}}
    <div class="meta-grid">
        {{-- Order info --}}
        <div class="meta-col">
            <div class="meta-card">
                <h4>Order Details</h4>
                <table class="meta-dl">
                    <tr><td class="lbl">Order #</td><td class="val">{{ $order->id }}</td></tr>
                    @if ($context !== 'retailer')
                        <tr><td class="lbl">Retailer</td><td class="val">{{ $order->retailer?->business_name ?? '—' }}</td></tr>
                    @endif
                    <tr><td class="lbl">Store</td><td class="val">{{ $order->store?->name ?? '—' }}</td></tr>
                    <tr><td class="lbl">Payment Method</td><td class="val">{{ strtoupper($order->payment_method ?? '—') }}</td></tr>
                    <tr><td class="lbl">Payment Currency</td><td class="val">{{ $order->payment_currency ?? 'PKR' }}</td></tr>
                    @if ($order->notes)
                        <tr><td class="lbl">Notes</td><td class="val">{{ $order->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Dates / Admin ops --}}
        <div class="meta-col">
            <div class="meta-card">
                <h4>Timeline & Operations</h4>
                <table class="meta-dl">
                    <tr><td class="lbl">Placed At</td><td class="val">{{ $order->created_at->format('d M Y, H:i') }}</td></tr>
                    @if ($order->payment_verified_at && $context !== 'huashu')
                        <tr><td class="lbl">Payment Verified</td><td class="val">{{ $order->payment_verified_at->format('d M Y, H:i') }}</td></tr>
                    @endif
                    @if ($order->transferred_to_huashu_at)
                        <tr><td class="lbl">Transferred At</td><td class="val">{{ $order->transferred_to_huashu_at->format('d M Y, H:i') }}</td></tr>
                    @endif
                    @if ($order->huashu_ref)
                        <tr><td class="lbl">Huashu Ref</td><td class="val">{{ $order->huashu_ref }}</td></tr>
                    @endif
                    @if ($order->oz_commission_pkr && $context === 'admin')
                        <tr><td class="lbl">OZ Commission</td><td class="val">PKR {{ number_format((float)$order->oz_commission_pkr, 0) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ── FX / Currency block ── --}}
    @php
        $usdRate  = (float) ($order->fx_usd_rate ?? 0);
        $cnyRate  = (float) ($order->fx_cny_rate ?? 0);
        $totalPkr = (float) $order->total_pkr;
    @endphp
    <div class="fx-box">
        <h4>Order Total in All Currencies</h4>
        <div class="fx-row">
            <div class="fx-cell">
                <div class="fx-currency">PKR (Primary)</div>
                <div class="fx-amount">PKR {{ number_format($totalPkr, 0) }}</div>
                <div class="fx-rate">Base currency</div>
            </div>
            <div class="fx-cell">
                <div class="fx-currency">USD</div>
                @if ($usdRate > 0)
                    <div class="fx-amount">$ {{ number_format($totalPkr * $usdRate, 2) }}</div>
                    <div class="fx-rate">1 USD = PKR {{ number_format(1 / $usdRate, 0) }}
                        @if ($order->fx_captured_at)({{ $order->fx_captured_at->format('d M Y') }})@endif
                    </div>
                @else
                    <div class="fx-amount">—</div>
                    <div class="fx-rate">No snapshot</div>
                @endif
            </div>
            <div class="fx-cell">
                <div class="fx-currency">CNY</div>
                @if ($cnyRate > 0)
                    <div class="fx-amount">¥ {{ number_format($totalPkr * $cnyRate, 2) }}</div>
                    <div class="fx-rate">1 CNY = PKR {{ number_format(1 / $cnyRate, 0) }}
                        @if ($order->fx_captured_at)({{ $order->fx_captured_at->format('d M Y') }})@endif
                    </div>
                @else
                    <div class="fx-amount">—</div>
                    <div class="fx-rate">No snapshot</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Items table ── --}}
    <div class="items-section">
        <div class="section-title">Order Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Variant</th>
                    <th class="num">Qty</th>
                    <th class="num">Unit Price (PKR)</th>
                    @if ($context === 'admin')
                        <th class="num">Huashu Price (PKR)</th>
                    @endif
                    <th class="num">Line Total (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product?->name_en ?? $item->product?->name_zh ?? '—' }}</td>
                        <td>{{ $item->variant_label ?? '—' }}</td>
                        <td class="num">{{ $item->qty }}</td>
                        <td class="num">{{ number_format((float) $item->unit_price_pkr, 2) }}</td>
                        @if ($context === 'admin')
                            <td class="num">{{ $item->huashu_unit_price_pkr ? number_format((float)$item->huashu_unit_price_pkr, 2) : '—' }}</td>
                        @endif
                        <td class="num">{{ number_format((float) $item->line_total_pkr, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $context === 'admin' ? 7 : 6 }}" style="text-align:center;color:#94a3b8;padding:12px;">No items found</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $context === 'admin' ? 5 : 4 }}"></td>
                    <td class="total-lbl">Total</td>
                    <td class="total-val">PKR {{ number_format($totalPkr, 0) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ── Status history ── --}}
    @if ($order->statusHistory && $order->statusHistory->count())
        <div class="timeline-section">
            <div class="section-title">Status History</div>
            <table class="timeline-table">
                @foreach ($order->statusHistory as $h)
                    @php
                        $from = $statusLabels[$h->from_status] ?? ucwords(str_replace('_',' ',$h->from_status ?? ''));
                        $to   = $statusLabels[$h->to_status]   ?? ucwords(str_replace('_',' ',$h->to_status   ?? ''));
                    @endphp
                    <tr>
                        <td class="from">{{ $from ?: '—' }}</td>
                        <td style="padding:5px 4px;color:#94a3b8;">→</td>
                        <td class="to">{{ $to }}</td>
                        <td class="by">{{ $h->changedBy?->name ?? 'System' }}</td>
                        @if ($h->note)<td class="by" style="font-style:italic;">"{{ $h->note }}"</td>@else<td></td>@endif
                        <td class="at">{{ $h->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        Generated {{ now()->format('d M Y, H:i') }} · OZ Tech B2B Platform
        @if ($context === 'admin') · Admin Export @elseif ($context === 'huashu') · Huashu Export @endif
    </div>

</div>
</body>
</html>
