<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStorePrice;

class CartService
{
    private string $sessionKey = 'retailer_cart';

    /** @return array<int, array{qty: int, price: float, name: string, unit: string}> */
    public function items(): array
    {
        return session($this->sessionKey, []);
    }

    public function add(int $productId, int $qty, float $price, string $name, string $unit): void
    {
        $cart = $this->items();

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += $qty;
        } else {
            $cart[$productId] = [
                'qty'   => $qty,
                'price' => $price,
                'name'  => $name,
                'unit'  => $unit,
            ];
        }

        session([$this->sessionKey => $cart]);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->items();

        if ($qty <= 0) {
            $this->remove($productId);
            return;
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] = $qty;
            session([$this->sessionKey => $cart]);
        }
    }

    public function remove(int $productId): void
    {
        $cart = $this->items();
        unset($cart[$productId]);
        session([$this->sessionKey => $cart]);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'qty'));
    }

    public function totalPkr(): float
    {
        $total = 0.0;
        foreach ($this->items() as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }
}
