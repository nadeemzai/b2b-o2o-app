<?php

namespace App\Livewire\Retailer\Cart;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockLevel;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CartPage extends Component
{
    public string $notes = '';

    public function updateQty(int $productId, int $qty, CartService $cart): void
    {
        $cart->update($productId, $qty);
    }

    public function remove(int $productId, CartService $cart): void
    {
        $cart->remove($productId);
    }

    public function placeOrder(CartService $cart): void
    {
        if ($cart->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        $retailer = auth()->user()->retailerProfile;
        $storeId  = $retailer->store_id;
        $items    = $cart->items();

        // Validate stock before placing
        foreach ($items as $productId => $item) {
            $stock = StockLevel::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->first();

            if (! $stock || $stock->qty_available < $item['qty']) {
                session()->flash('error', "Insufficient stock for: {$item['name']}");
                return;
            }
        }

        DB::transaction(function () use ($cart, $retailer, $storeId, $items) {
            $order = Order::create([
                'retailer_id'    => $retailer->id,
                'store_id'       => $storeId,
                'status'         => Order::STATUS_PENDING,
                'total_pkr'      => $cart->totalPkr(),
                'payment_method' => 'cod',
                'notes'          => $this->notes ?: null,
            ]);

            foreach ($items as $productId => $item) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $productId,
                    'qty'         => $item['qty'],
                    'unit_price'  => $item['price'],
                    'line_total'  => $item['price'] * $item['qty'],
                ]);

                // Reserve stock
                StockLevel::where('product_id', $productId)
                    ->where('store_id', $storeId)
                    ->increment('qty_reserved', $item['qty']);
            }

            $cart->clear();
        });

        session()->flash('success', 'Order placed successfully! Your store will prepare it shortly.');
        $this->redirect(route('retailer.orders'), navigate: true);
    }

    public function render(CartService $cart)
    {
        $items    = $cart->items();
        $products = [];

        if (! empty($items)) {
            $productModels = Product::whereIn('id', array_keys($items))->get()->keyBy('id');
            foreach ($items as $productId => $item) {
                $products[$productId] = array_merge($item, [
                    'product' => $productModels[$productId] ?? null,
                ]);
            }
        }

        return view('livewire.retailer.cart.cart-page', [
            'cartProducts' => $products,
            'total'        => $cart->totalPkr(),
        ])->layout('layouts.retailer', ['title' => 'My Cart']);
    }
}
