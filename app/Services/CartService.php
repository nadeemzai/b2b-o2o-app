<?php

namespace App\Services;

/**
 * Cart session structure:
 *
 *  Non-variant item:   key = (int) $productId
 *    ['product_id' => int, 'qty' => int, 'price' => float, 'price_mode' => 'per_unit'|'bundle', 'name' => str, 'unit' => str, 'moq' => int]
 *
 *  Variant item:       key = "p{productId}_v{variantOptionId}"  (string)
 *    ['product_id' => int, 'variant_option_id' => int, 'variant_label' => str,
 *     'qty' => int, 'price' => float, 'price_mode' => 'per_unit'|'bundle', 'name' => str, 'unit' => str, 'moq' => int]
 */
class CartService
{
    private string $sessionKey = 'retailer_cart';

    /**
     * @return array<int|string, array>
     */
    public function items(): array
    {
        return session($this->sessionKey, []);
    }

    // ──────────────────────────────────────────────
    // Adding items
    // ──────────────────────────────────────────────

    /**
     * Add or increment a non-variant product in the cart.
     */
    public function add(int $productId, int $qty, float $price, string $name, string $unit, int $moq = 1, string $priceMode = 'per_unit'): void
    {
        $cart = $this->items();
        $moq  = max(1, $moq);
        $key  = $productId;   // integer key for plain products

        if (isset($cart[$key])) {
            // Replace qty and reprice — user set a specific quantity on the detail page,
            // so "Add to Cart" means "I want this many total", not "add more on top".
            $cart[$key]['qty']        = max($moq, $qty);
            $cart[$key]['price']      = $price;
            $cart[$key]['price_mode'] = $priceMode;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'qty'        => max($moq, $qty),
                'price'      => $price,
                'price_mode' => $priceMode,
                'name'       => $name,
                'unit'       => $unit,
                'moq'        => $moq,
            ];
        }

        session([$this->sessionKey => $cart]);
    }

    /**
     * Add or update a specific variant option in the cart.
     *
     * @param string $variantLabel  Human-readable "Color: Red" label for the cart display.
     */
    public function addVariant(
        int    $productId,
        int    $variantOptionId,
        string $variantLabel,
        int    $qty,
        float  $price,
        string $name,
        string $unit,
        int    $moq = 1,
        string $priceMode = 'per_unit'
    ): void {
        $cart = $this->items();
        $moq  = max(1, $moq);
        $key  = "p{$productId}_v{$variantOptionId}";

        if (isset($cart[$key])) {
            if ($qty <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['qty']        = max($moq, $qty);   // enforce MOQ floor
                $cart[$key]['price']      = $price;             // update to current tier price
                $cart[$key]['price_mode'] = $priceMode;
            }
        } else {
            if ($qty > 0) {
                $cart[$key] = [
                    'product_id'        => $productId,
                    'variant_option_id' => $variantOptionId,
                    'variant_label'     => $variantLabel,
                    'qty'               => max($moq, $qty),   // enforce MOQ floor on add
                    'price'             => $price,
                    'price_mode'        => $priceMode,
                    'name'              => $name,
                    'unit'              => $unit,
                    'moq'               => $moq,
                ];
            }
        }

        session([$this->sessionKey => $cart]);
    }

    // ──────────────────────────────────────────────
    // Updating / removing
    // ──────────────────────────────────────────────

    /**
     * Update qty for any cart line (works for both int and string keys).
     * Silently clamps to MOQ — never drops below it.
     */
    public function updateByKey(int|string $key, int $qty): void
    {
        $cart = $this->items();

        if ($qty <= 0) {
            $this->removeByKey($key);
            return;
        }

        if (isset($cart[$key])) {
            $moq = (int) ($cart[$key]['moq'] ?? 1);
            $cart[$key]['qty'] = max($moq, $qty);
            session([$this->sessionKey => $cart]);
        }
    }

    /**
     * Update the stored unit price for a cart line (called when qty change crosses a tier).
     */
    public function updatePriceByKey(int|string $key, float $price, ?string $priceMode = null): void
    {
        $cart = $this->items();
        if (isset($cart[$key])) {
            $cart[$key]['price'] = $price;
            if ($priceMode !== null) {
                $cart[$key]['price_mode'] = $priceMode;
            }
            session([$this->sessionKey => $cart]);
        }
    }

    /**
     * @deprecated  Use updateByKey(). Kept for backward compatibility.
     */
    public function update(int $productId, int $qty): void
    {
        $this->updateByKey($productId, $qty);
    }

    public function removeByKey(int|string $key): void
    {
        $cart = $this->items();
        unset($cart[$key]);
        session([$this->sessionKey => $cart]);
    }

    /**
     * @deprecated  Use removeByKey(). Kept for backward compatibility.
     */
    public function remove(int $productId): void
    {
        $this->removeByKey($productId);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    // ──────────────────────────────────────────────
    // Totals
    // ──────────────────────────────────────────────

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'qty'));
    }

    public function totalPkr(): float
    {
        $total = 0.0;
        foreach ($this->items() as $item) {
            if (($item['price_mode'] ?? 'per_unit') === 'bundle') {
                // price_pkr is a flat bundle total — add once regardless of qty
                $total += $item['price'];
            } else {
                $total += $item['price'] * $item['qty'];
            }
        }
        return $total;
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }
}
