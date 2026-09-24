<?php

namespace App\Http\Controllers\Export;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderExportController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // RETAILER  — scoped to authenticated retailer's own orders
    // ──────────────────────────────────────────────────────────────────────────

    public function retailerListing(Request $request)
    {
        $retailer = $request->user()->retailerProfile;
        abort_if(! $retailer, 403);

        $query = Order::query()
            ->where('retailer_id', $retailer->id)
            ->orderByDesc('created_at');

        $this->applyCommonFilters($query, $request);

        $format   = $request->query('format', 'xlsx');
        $filename = 'my-orders-' . now()->format('Ymd-His');

        return $this->streamSpreadsheet(
            new OrdersExport($query, 'retailer'),
            $filename,
            $format,
        );
    }

    public function retailerOrderPdf(Request $request, int $orderId)
    {
        $retailer = $request->user()->retailerProfile;
        abort_if(! $retailer, 403);

        $order = Order::with(['items.product', 'store', 'statusHistory.changedBy'])
            ->where('retailer_id', $retailer->id)
            ->findOrFail($orderId);

        return $this->streamOrderPdf($order, 'retailer');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ADMIN  — all orders, all filters
    // ──────────────────────────────────────────────────────────────────────────

    public function adminListing(Request $request)
    {
        $query = Order::query()->orderByDesc('created_at');

        $this->applyCommonFilters($query, $request);

        // Admin also supports filtering by store
        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->query('store_id'));
        }

        $format   = $request->query('format', 'xlsx');
        $filename = 'orders-admin-' . now()->format('Ymd-His');

        return $this->streamSpreadsheet(
            new OrdersExport($query, 'admin'),
            $filename,
            $format,
        );
    }

    public function adminOrderPdf(Request $request, int $orderId)
    {
        $order = Order::with(['items.product', 'store', 'retailer', 'statusHistory.changedBy',
                              'paymentVerifiedBy', 'transferredBy'])
            ->findOrFail($orderId);

        return $this->streamOrderPdf($order, 'admin');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HUASHU  — only transferred/fulfilling/delivered orders
    // ──────────────────────────────────────────────────────────────────────────

    public function huashuListing(Request $request)
    {
        $query = Order::query()
            ->forHuashu()
            ->orderByDesc('transferred_to_huashu_at');

        $this->applyCommonFilters($query, $request);

        $format   = $request->query('format', 'xlsx');
        $filename = 'orders-huashu-' . now()->format('Ymd-His');

        return $this->streamSpreadsheet(
            new OrdersExport($query, 'huashu'),
            $filename,
            $format,
        );
    }

    public function huashuOrderPdf(Request $request, int $orderId)
    {
        $order = Order::with(['items.product', 'store', 'retailer', 'statusHistory.changedBy'])
            ->forHuashu()
            ->findOrFail($orderId);

        return $this->streamOrderPdf($order, 'huashu');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Shared helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function applyCommonFilters(\Illuminate\Database\Eloquent\Builder $query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('retailer', fn ($r) =>
                      $r->where('business_name', 'ilike', "%{$search}%")
                  );
            });
        }

        // Optional date range (YYYY-MM-DD)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }
    }

    private function streamSpreadsheet(OrdersExport $export, string $filename, string $format)
    {
        $format = in_array($format, ['xlsx', 'csv'], true) ? $format : 'xlsx';

        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    private function streamOrderPdf(Order $order, string $context)
    {
        $pdf = Pdf::loadView('exports.order-pdf', [
            'order'   => $order,
            'context' => $context,
        ])->setPaper('a4', 'portrait');

        $filename = 'order-' . $order->id . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
