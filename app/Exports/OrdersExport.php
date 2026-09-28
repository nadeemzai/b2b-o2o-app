<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query  Already-scoped + filtered query
     * @param string $context  'admin' | 'huashu' | 'retailer'
     */
    public function __construct(
        protected \Illuminate\Database\Eloquent\Builder $query,
        protected string $context = 'admin',
    ) {}

    public function title(): string
    {
        return 'Orders';
    }

    // ──────────────────────────────────────────────
    // Data rows
    // ──────────────────────────────────────────────

    public function collection(): Collection
    {
        return $this->query
            ->with(['retailer', 'store', 'items'])
            ->get()
            ->map(fn (Order $order) => $this->buildRow($order));
    }

    private function buildRow(Order $order): array
    {
        $usdRate  = (float) ($order->fx_usd_rate ?? 0);
        $cnyRate  = (float) ($order->fx_cny_rate ?? 0);
        $totalPkr = (float) $order->total_pkr;

        $statusMap = [
            'pending'          => 'Pending',
            'payment_verified' => 'Payment Verified',
            'transferred'      => 'Transferred to Huashu',
            'fulfilling'       => 'Fulfilling',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
        ];

        // ── Shared columns (all contexts) ─────────────────────────────
        $row = [
            $order->id,
            $statusMap[$order->status] ?? ucwords(str_replace('_', ' ', $order->status)),
            $order->items->count(),
            round($totalPkr, 2),
            $usdRate > 0 ? round($totalPkr * $usdRate, 2) : '',
            $cnyRate > 0 ? round($totalPkr * $cnyRate, 2) : '',
            strtoupper($order->payment_method ?? ''),
            $order->payment_currency ?? 'PKR',
            $usdRate > 0 ? $usdRate : '',
            $cnyRate > 0 ? $cnyRate : '',
            $order->fx_captured_at?->format('Y-m-d H:i') ?? '',
            $order->created_at->format('Y-m-d H:i'),
        ];

        // ── Admin: full columns ────────────────────────────────────────
        if ($this->context === 'admin') {
            $row[] = $order->retailer?->business_name ?? '';
            $row[] = $order->store?->name ?? '';
            $row[] = $order->payment_verified_at?->format('Y-m-d H:i') ?? '';
            $row[] = $order->oz_commission_pkr ? round((float) $order->oz_commission_pkr, 2) : '';
            $row[] = $order->transferred_to_huashu_at?->format('Y-m-d H:i') ?? '';
            $row[] = $order->huashu_ref ?? '';
            $row[] = $order->notes ?? '';
        }

        // ── Huashu: fulfillment-relevant columns ───────────────────────
        if ($this->context === 'huashu') {
            $row[] = $order->retailer?->business_name ?? '';
            $row[] = $order->store?->name ?? '';
            $row[] = $order->transferred_to_huashu_at?->format('Y-m-d H:i') ?? '';
            $row[] = $order->huashu_ref ?? '';
        }

        // ── Retailer: store only (retailer known from auth) ────────────
        if ($this->context === 'retailer') {
            $row[] = $order->store?->name ?? '';
        }

        return $row;
    }

    // ──────────────────────────────────────────────
    // Headings
    // ──────────────────────────────────────────────

    public function headings(): array
    {
        $base = [
            'Order #', 'Status', 'Items', 'Total (PKR)',
            'USD Equiv', 'CNY Equiv',
            'Payment Method', 'Payment Currency',
            'FX USD Rate', 'FX CNY Rate', 'FX Captured At',
            'Placed At',
        ];

        return match ($this->context) {
            'admin'    => array_merge($base, [
                'Retailer', 'Store',
                'Payment Verified At', 'OZ Commission (PKR)',
                'Transferred At', 'Huashu Ref', 'Notes',
            ]),
            'huashu'   => array_merge($base, [
                'Retailer', 'Store', 'Transferred At', 'Huashu Ref',
            ]),
            'retailer' => array_merge($base, ['Store']),
            default    => $base,
        };
    }

    // ──────────────────────────────────────────────
    // Styling — navy header row
    // ──────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
            ],
        ];
    }
}
