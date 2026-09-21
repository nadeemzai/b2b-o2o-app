<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\StockReservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    // ──────────────────────────────────────────────
    // Place Order
    // ──────────────────────────────────────────────

    /**
     * Atomically place a new order.
     *
     * Algorithm:
     *  1. Lock stock_levels rows (SELECT FOR UPDATE) for all ordered products.
     *  2. Validate each product has a price and sufficient stock.
     *  3. Create the Order + OrderItems with frozen prices.
     *  4. Increment qty_reserved on StockLevel.
     *  5. Insert StockReservation rows.
     *  6. Write first OrderStatusHistory row.
     *
     * @param  Retailer  $retailer
     * @param  array     $items  [ ['product_id' => int, 'qty' => int], ... ]
     * @return Order
     *
     * @throws InsufficientStockException
     */
    public function placeOrder(Retailer $retailer, array $items): Order
    {
        return DB::transaction(function () use ($retailer, $items) {

            $storeId    = $retailer->store_id;
            $productIds = array_column($items, 'product_id');

            // ── 1. Lock stock rows ─────────────────────────────────────────
            /** @var Collection<int, StockLevel> $stockMap (keyed by product_id) */
            $stockMap = StockLevel::query()
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()        // SELECT FOR UPDATE — prevents race conditions
                ->get()
                ->keyBy('product_id');

            // ── 2. Validate prices & stock ─────────────────────────────────
            $priceMap = Product::query()
                ->join('product_store_prices as psp', 'psp.product_id', '=', 'products.id')
                ->where('psp.store_id', $storeId)
                ->where('psp.is_active', true)
                ->whereIn('products.id', $productIds)
                ->select('products.id', 'products.name', 'psp.price_pkr')
                ->get()
                ->keyBy('id');

            $totalPkr  = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $qty       = $item['qty'];

                abort_unless(isset($priceMap[$productId]), 422, "Product #{$productId} not available in your store.");

                $stock = $stockMap->get($productId);
                abort_unless($stock, 422, "No stock record for product #{$productId} in this store.");

                if ($stock->qty_available < $qty) {
                    throw new InsufficientStockException(
                        productId:   $productId,
                        productName: $priceMap[$productId]->name,
                        requested:   $qty,
                        available:   $stock->qty_available,
                    );
                }

                $unitPrice   = (float) $priceMap[$productId]->price_pkr;
                $totalPkr   += $unitPrice * $qty;
                $lineItems[] = [
                    'product_id'     => $productId,
                    'qty'            => $qty,
                    'unit_price_pkr' => $unitPrice,
                    'stock'          => $stock,
                ];
            }

            // ── 3. Create Order ────────────────────────────────────────────
            /** @var Order $order */
            $order = Order::create([
                'retailer_id'    => $retailer->id,
                'store_id'       => $storeId,
                'status'         => Order::STATUS_PENDING,
                'total_pkr'      => $totalPkr,
                'payment_method' => 'cod',
            ]);

            // ── 4. Create OrderItems + reserve stock ───────────────────────
            foreach ($lineItems as $line) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $line['product_id'],
                    'qty'            => $line['qty'],
                    'unit_price_pkr' => $line['unit_price_pkr'],
                ]);

                // Increment qty_reserved (decrements qty_available via generated column)
                $line['stock']->increment('qty_reserved', $line['qty']);

                // Soft-reserve record
                StockReservation::create([
                    'order_id'   => $order->id,
                    'product_id' => $line['product_id'],
                    'store_id'   => $storeId,
                    'qty'        => $line['qty'],
                    'status'     => 'reserved',
                ]);
            }

            // ── 5. First status history row ────────────────────────────────
            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => null,
                'to_status'          => Order::STATUS_PENDING,
                'changed_by_user_id' => $retailer->user_id,
                'note'               => 'Order placed by retailer.',
            ]);

            return $order->load('items.product', 'statusHistory');
        });
    }

    // ──────────────────────────────────────────────
    // Cancel Order (by retailer)
    // ──────────────────────────────────────────────

    public function cancelOrder(Order $order, int $cancelledByUserId): Order
    {
        return DB::transaction(function () use ($order, $cancelledByUserId) {

            $from = $order->status;
            $order->update(['status' => Order::STATUS_CANCELLED]);

            // Release all active reservations
            $this->releaseReservations($order);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_CANCELLED,
                'changed_by_user_id' => $cancelledByUserId,
                'note'               => 'Cancelled.',
            ]);

            return $order->fresh();
        });
    }

    // ──────────────────────────────────────────────
    // Deliver Order (by rider / manager)
    // ──────────────────────────────────────────────

    /**
     * Mark as delivered, consume reservations, record stock outbound movement,
     * and record collected COD amount.
     */
    public function deliverOrder(Order $order, float $collectedPkr, int $deliveredByUserId): Order
    {
        return DB::transaction(function () use ($order, $collectedPkr, $deliveredByUserId) {

            $from = $order->status;
            $order->update([
                'status'        => Order::STATUS_DELIVERED,
                'collected_pkr' => $collectedPkr,
            ]);

            // Consume reservations → deduct qty_on_hand + qty_reserved
            $reservations = $order->stockReservations()->where('status', 'reserved')->get();
            foreach ($reservations as $reservation) {
                $reservation->update(['status' => 'consumed']);

                $stock = StockLevel::where('product_id', $reservation->product_id)
                    ->where('store_id', $reservation->store_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stock->decrement('qty_on_hand',  $reservation->qty);
                $stock->decrement('qty_reserved', $reservation->qty);

                StockMovement::create([
                    'product_id'         => $reservation->product_id,
                    'store_id'           => $reservation->store_id,
                    'order_id'           => $order->id,
                    'type'               => 'outbound',
                    'qty'                => -$reservation->qty,
                    'note'               => "Delivered — order #{$order->id}",
                    'created_by_user_id' => $deliveredByUserId,
                ]);
            }

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_DELIVERED,
                'changed_by_user_id' => $deliveredByUserId,
                'note'               => "COD collected: PKR {$collectedPkr}",
            ]);

            return $order->fresh();
        });
    }

    // ──────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────

    private function releaseReservations(Order $order): void
    {
        $reservations = $order->stockReservations()->where('status', 'reserved')->get();

        foreach ($reservations as $reservation) {
            $reservation->update(['status' => 'released']);

            StockLevel::where('product_id', $reservation->product_id)
                ->where('store_id', $reservation->store_id)
                ->decrement('qty_reserved', $reservation->qty);
        }
    }
}
