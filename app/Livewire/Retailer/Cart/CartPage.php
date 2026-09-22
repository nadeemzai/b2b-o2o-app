<?php

namespace App\Livewire\Retailer\Cart;

use App\Exceptions\InsufficientStockException;
use App\Models\Retailer;
use App\Services\CartService;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.retailer')]
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

    public function placeOrder(CartService $cart, OrderService $orderService): void
    {
        if ($cart->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        /** @var Retailer $retailer */
        $retailer = auth()->user()->retailerProfile;

        // Build items array in the format OrderService expects
        $items = collect($cart->items())
            ->map(fn ($item, $productId) => [
                'product_id' => (int) $productId,
                'qty'        => $item['qty'],
            ])
            ->values()
            ->all();

        try {
            $order = $orderService->placeOrder($retailer, $items);
        } catch (InsufficientStockException $e) {
            session()->flash('error', "Insufficient stock for: {$e->productName}");
            return;
        }

        // Append optional notes (not part of OrderService signature — update after)
        if ($this->notes !== '') {
            $order->update(['notes' => $this->notes]);
        }

        $cart->clear();

        session()->flash('success', 'Order placed successfully! Your store will prepare it shortly.');
        $this->redirect(route('retailer.orders'), navigate: true);
    }

    public function render(CartService $cart)
    {
        $items    = $cart->items();
        $products = [];

        if (! empty($items)) {
            $productModels = \App\Models\Product::whereIn('id', array_keys($items))->get()->keyBy('id');
            foreach ($items as $productId => $item) {
                $products[$productId] = array_merge($item, [
                    'product' => $productModels[$productId] ?? null,
                ]);
            }
        }

        return view('livewire.retailer.cart.cart-page', [
            'cartProducts' => $products,
            'total'        => $cart->totalPkr(),
        ])->title('My Cart');
    }
}
