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
use App\Events\OrderDelivered;
use App\Events\OrderFulfilling;
use App\Events\OrderPlaced;
use App\Events\OrderTransferred;
use App\Events\PaymentVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderService
{
    public function __construct(
        private readonly PricingService $pricing,
    ) {}

    // ──────────────────────────────────────────────
    // Place Order
    // ──────────────────────────────────────────────

    /**
     * Atomically place a new order.
     *
     * Algorithm:
     *  1. Lock stock_levels rows (SELECT FOR UPDATE) for all ordered products.
     *  2. Validate stock is sufficient and qty meets MOQ.
     *  3. Snapshot retailer price + Huashu price + commission_rate per item.
     *  4. Create the Order + OrderItems with frozen prices.
     *  5. Increment qty_reserved on StockLevel.
     *  6. Insert StockReservation rows.
     *  7. Write first OrderStatusHistory row.
     *
     * @param  Retailer  $retailer
     * @param  array     $items  [ ['product_id' => int, 'qty' => int], ... ]
     * @return Order
     *
     * @throws InsufficientStockException
     */
    public function placeOrder(Retailer $retailer, array $items): Order
    {
        $order = DB::transaction(function () use ($retailer, $items) {

            $storeId    = $retailer->store_id;
            $productIds = array_column($items, 'product_id');

            // ── 1. Lock stock rows ─────────────────────────────────────────
            /** @var Collection<int, StockLevel> $stockMap (keyed by product_id) */
            $stockMap = StockLevel::query()
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            // ── 2. Load products with Huashu pricing ───────────────────────
            /** @var Collection<int, Product> $productMap (keyed by id) */
            $productMap = Product::withPrice()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            // Batch load commission rates for all categories in one query
            $categoryIds     = $productMap->pluck('category_id')->unique()->values()->all();
            $commissionRates = $this->pricing->ratesForCategories($categoryIds);

            // ── 3. Validate stock + MOQ; compute line prices ───────────────
            $totalPkr  = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $qty       = (int) $item['qty'];

                abort_unless(isset($productMap[$productId]), 422, "Product #{$productId} not found.");

                /** @var Product $product */
                $product = $productMap[$productId];

                abort_unless(
                    $product->huashu_base_price_pkr !== null,
                    422,
                    "Product \"{$product->name_en}\" has no price set — cannot be ordered."
                );

                // MOQ enforcement
                $moq = (int) ($product->moq ?? 1);
                abort_unless(
                    $qty >= $moq,
                    422,
                    "Minimum order quantity for \"{$product->name_en}\" is {$moq} units."
                );

                // Stock check (null stock row = untracked = always available)
                $stock = $stockMap->get($productId);
                if ($stock !== null && $stock->qty_available < $qty) {
                    throw new InsufficientStockException(
                        productId:   $productId,
                        productName: $product->name_en,
                        requested:   $qty,
                        available:   $stock->qty_available,
                    );
                }

                // Price snapshots
                $commissionRate    = $commissionRates[$product->category_id] ?? 0.0;
                $huashuUnitPrice   = (float) $product->huashu_base_price_pkr;
                $retailerUnitPrice = $this->pricing->retailerPrice($product);

                $totalPkr   += $retailerUnitPrice * $qty;
                $lineItems[] = [
                    'product_id'           => $productId,
                    'variant_option_id'    => $item['variant_option_id'] ?? null,
                    'variant_label'        => $item['variant_label'] ?? null,
                    'qty'                  => $qty,
                    'unit_price_pkr'       => $retailerUnitPrice,
                    'huashu_unit_price_pkr'=> $huashuUnitPrice,
                    'commission_rate'      => $commissionRate,
                    'stock'                => $stock,
                    'product_name'         => $product->name_en,
                ];
            }

            // ── 4. Create Order ────────────────────────────────────────────
            /** @var Order $order */
            $order = Order::create([
                'retailer_id'    => $retailer->id,
                'store_id'       => $storeId,
                'status'         => Order::STATUS_PENDING,
                'total_pkr'      => round($totalPkr, 2),
                'payment_method' => 'cod',
            ]);

            // ── 5. Create OrderItems + reserve stock ───────────────────────
            foreach ($lineItems as $line) {
                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $line['product_id'],
                    'variant_option_id'    => $line['variant_option_id'] ?? null,
                    'variant_label'        => $line['variant_label'] ?? null,
                    'qty'                  => $line['qty'],
                    'unit_price_pkr'       => $line['unit_price_pkr'],
                    'huashu_unit_price_pkr'=> $line['huashu_unit_price_pkr'],
                    'commission_rate'      => $line['commission_rate'],
                ]);

                if ($line['stock']) {
                    $line['stock']->increment('qty_reserved', $line['qty']);

                    StockReservation::create([
                        'order_id'   => $order->id,
                        'product_id' => $line['product_id'],
                        'store_id'   => $storeId,
                        'qty'        => $line['qty'],
                        'status'     => 'reserved',
                    ]);
                }
            }

            // ── 6. First status history row ────────────────────────────────
            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => null,
                'to_status'          => Order::STATUS_PENDING,
                'changed_by_user_id' => $retailer->user_id,
                'note'               => 'Order placed by retailer.',
            ]);

            return $order->load('items.product', 'statusHistory');
        });

        Event::dispatch(new OrderPlaced($order));

        return $order;
    }

    // ──────────────────────────────────────────────
    // Verify Payment (OZ admin)
    // ──────────────────────────────────────────────

    /**
     * Mark payment as verified; order moves pending → payment_verified.
     */
    public function verifyPayment(Order $order, int $userId, ?string $note = null): Order
    {
        abort_unless($order->canVerifyPayment(), 422, 'Order payment can only be verified when status is pending.');

        $order = DB::transaction(function () use ($order, $userId, $note) {
            $from = $order->status;

            $order->update([
                'status'               => Order::STATUS_PAYMENT_VERIFIED,
                'payment_verified_at'  => now(),
                'payment_verified_by'  => $userId,
            ]);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_PAYMENT_VERIFIED,
                'changed_by_user_id' => $userId,
                'note'               => $note ?? 'Payment confirmed by admin.',
            ]);

            return $order->fresh();
        });

        Event::dispatch(new PaymentVerified($order));

        return $order;
    }

    // ──────────────────────────────────────────────
    // Transfer to Huashu (OZ admin)
    // ──────────────────────────────────────────────

    /**
     * Transfer order to Huashu for fulfillment.
     * Computes and stores total OZ commission at transfer time.
     * Order moves payment_verified → transferred.
     */
    public function transferToHuashu(Order $order, int $userId, ?string $note = null): Order
    {
        abort_unless($order->canTransferToHuashu(), 422, 'Order can only be transferred after payment is verified.');

        $order = DB::transaction(function () use ($order, $userId, $note) {
            $from = $order->status;

            // Compute total commission from all items
            $order->loadMissing('items');
            $totalCommission = $order->items->sum(
                fn (OrderItem $item) => $item->ozCommissionPkr()
            );

            $order->update([
                'status'                 => Order::STATUS_TRANSFERRED,
                'transferred_to_huashu_at' => now(),
                'transferred_by'         => $userId,
                'oz_commission_pkr'      => round($totalCommission, 2),
            ]);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_TRANSFERRED,
                'changed_by_user_id' => $userId,
                'note'               => $note ?? 'Order transferred to Huashu for fulfillment.',
            ]);

            return $order->fresh();
        });

        Event::dispatch(new OrderTransferred($order));

        return $order;
    }

    // ──────────────────────────────────────────────
    // Mark Fulfilling (Huashu)
    // ──────────────────────────────────────────────

    /**
     * Huashu sets order to fulfilling with their reference number.
     * Order moves transferred → fulfilling.
     */
    public function markFulfilling(Order $order, int $userId, ?string $huashuRef = null, ?string $note = null): Order
    {
        abort_unless($order->canMarkFulfilling(), 422, 'Order must be in transferred status to mark as fulfilling.');

        $order = DB::transaction(function () use ($order, $userId, $huashuRef, $note) {
            $from = $order->status;

            $updates = ['status' => Order::STATUS_FULFILLING];
            if ($huashuRef) {
                $updates['huashu_ref'] = $huashuRef;
            }

            $order->update($updates);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_FULFILLING,
                'changed_by_user_id' => $userId,
                'note'               => $note ?? ('Huashu processing.' . ($huashuRef ? " Ref: {$huashuRef}" : '')),
            ]);

            return $order->fresh();
        });

        Event::dispatch(new OrderFulfilling($order));

        return $order;
    }

    // ──────────────────────────────────────────────
    // Cancel Order (retailer or admin)
    // ──────────────────────────────────────────────

    public function cancelOrder(Order $order, int $cancelledByUserId, ?string $note = null): Order
    {
        abort_unless($order->isCancellable(), 422, 'Order cannot be cancelled at this stage.');

        return DB::transaction(function () use ($order, $cancelledByUserId, $note) {
            $from = $order->status;
            $order->update(['status' => Order::STATUS_CANCELLED]);

            $this->releaseReservations($order);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'from_status'        => $from,
                'to_status'          => Order::STATUS_CANCELLED,
                'changed_by_user_id' => $cancelledByUserId,
                'note'               => $note ?? 'Cancelled.',
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
        $order = DB::transaction(function () use ($order, $collectedPkr, $deliveredByUserId) {

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

        Event::dispatch(new OrderDelivered($order));

        return $order;
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
