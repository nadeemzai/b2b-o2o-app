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
    public array  $moqWarnings = [];

    /**
     * Update qty for any cart line — works for both plain product keys (int)
     * and variant keys (string like "p5_v12").
     */
    public function updateQty(string $key, int $qty, CartService $cart): void
    {
        $items = $cart->items();
        $key   = is_numeric($key) ? (int) $key : $key;

        if (isset($items[$key])) {
            $moq = (int) ($items[$key]['moq'] ?? 1);
            if ($qty > 0 && $qty < $moq) {
                $this->moqWarnings[(string) $key] = "Minimum order quantity is {$moq} " . ($moq === 1 ? 'unit' : 'units') . '.';
            } else {
                unset($this->moqWarnings[(string) $key]);
            }
        }

        $cart->updateByKey($key, $qty);
    }

    public function remove(string $key, CartService $cart): void
    {
        $key = is_numeric($key) ? (int) $key : $key;
        unset($this->moqWarnings[(string) $key]);
        $cart->removeByKey($key);
    }

    public function placeOrder(CartService $cart, OrderService $orderService)
    {
        if ($cart->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        /** @var Retailer $retailer */
        $retailer = auth()->user()->retailerProfile;

        // Build items array in the format OrderService expects.
        // Variant items and plain items both carry 'product_id' in their data.
        $items = collect($cart->items())
            ->map(fn ($item) => [
                'product_id'        => (int) $item['product_id'],
                'qty'               => $item['qty'],
                'variant_option_id' => isset($item['variant_option_id']) ? (int) $item['variant_option_id'] : null,
                'variant_label'     => $item['variant_label'] ?? null,
            ])
            ->values()
            ->all();

        try {
            $order = $orderService->placeOrder($retailer, $items);
        } catch (InsufficientStockException $e) {
            session()->flash('error', "Insufficient stock for \"{$e->productName}\". Available: {$e->available}, requested: {$e->requested}.");
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
            return;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        if ($this->notes !== '') {
            $order->update(['notes' => $this->notes]);
        }

        $cart->clear();

        session()->put('order_placed_success', 'Order placed successfully! Your store will prepare it shortly.');
        $this->redirect(route('retailer.orders'));
    }

    public function render(CartService $cart)
    {
        $items    = $cart->items();
        $products = [];

        if (! empty($items)) {
            // Collect unique product IDs from all cart items (works for both key types)
            $productIds    = array_unique(array_column(array_values($items), 'product_id'));
            $productModels = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $key => $item) {
                $productId         = $item['product_id'];
                $products[$key]    = array_merge($item, [
                    'cart_key' => $key,
                    'product'  => $productModels[$productId] ?? null,
                ]);
            }
        }

        return view('livewire.retailer.cart.cart-page', [
            'cartProducts' => $products,
            'total'        => $cart->totalPkr(),
        ])->title('My Cart');
    }
}
