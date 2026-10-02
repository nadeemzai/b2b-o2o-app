<?php

namespace App\Services;

use App\Models\CategoryCommission;
use App\Models\Product;

class PricingService
{
    /**
     * Commission rate for a single category (0.0–1.0).
     */
    public function commissionRate(int $categoryId): float
    {
        $row = CategoryCommission::query()
            ->where('category_id', $categoryId)
            ->where('effective_from', '<=', now()->toDateString())
            ->latest('effective_from')
            ->first();

        return $row ? (float) $row->commission_rate : 0.0;
    }

    /**
     * Retailer-facing price for a product, or null if no base price is set.
     */
    public function retailerPrice(Product $product): ?float
    {
        if (! $product->huashu_base_price_pkr) {
            return null;
        }

        $rate = $this->commissionRate($product->category_id);

        return round((float) $product->huashu_base_price_pkr * (1 + $rate), 2);
    }

    /**
     * Batch-load commission rates for a set of category IDs (avoids N+1 on list pages).
     *
     * @param  int[]  $categoryIds
     * @return array<int, float>  keyed by category_id
     */
    public function ratesForCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $today = now()->toDateString();

        $rates = CategoryCommission::query()
            ->whereIn('category_id', $categoryIds)
            ->where('effective_from', '<=', $today)
            ->orderBy('category_id')
            ->orderByDesc('effective_from')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($group) => (float) $group->first()->commission_rate)
            ->toArray();

        // Ensure every requested ID has a key (default 0 if no rate defined)
        foreach ($categoryIds as $id) {
            if (! isset($rates[$id])) {
                $rates[$id] = 0.0;
            }
        }

        return $rates;
    }

    // ──────────────────────────────────────────────
    // Volume / Tier Pricing
    // ──────────────────────────────────────────────

    /**
     * Resolve the retailer-facing unit price for a given quantity,
     * applying tiered pricing when tiers exist.
     *
     * Algorithm: walk tiers from highest min_qty downward.
     * Return the first tier whose min_qty <= $qty.
     * Falls back to flat retailerPrice() when no tiers are configured.
     *
     * @param  Product  $product  Must have priceTiers relation loaded (or will eager-load).
     * @param  int      $qty      Quantity being ordered / displayed.
     * @return float|null         Resolved unit price, or null if product has no price.
     */
    public function tierPrice(Product $product, int $qty): ?float
    {
        $tiers = $product->relationLoaded('priceTiers')
            ? $product->priceTiers
            : $product->priceTiers()->orderBy('min_qty')->get();

        if ($tiers->isEmpty()) {
            return $this->retailerPrice($product);
        }

        foreach ($tiers->sortByDesc('min_qty') as $tier) {
            if ($qty >= $tier->min_qty) {
                return (float) $tier->price_pkr;
            }
        }

        // qty below all tier lower-bounds: show entry price (lowest tier)
        return (float) $tiers->first()->price_pkr;
    }

    /**
     * Return all tiers as a plain array suitable for JSON / Alpine.js.
     *
     * @return array<int, array{min_qty:int,max_qty:int|null,price_pkr:float,label:string|null}>
     */
    public function tiersArray(Product $product): array
    {
        $tiers = $product->relationLoaded('priceTiers')
            ? $product->priceTiers
            : $product->priceTiers()->orderBy('min_qty')->get();

        return $tiers->map(fn ($t) => [
            'min_qty'   => (int)   $t->min_qty,
            'max_qty'   => $t->max_qty !== null ? (int) $t->max_qty : null,
            'price_pkr' => (float) $t->price_pkr,
            'label'     => $t->label,
        ])->values()->all();
    }

}
