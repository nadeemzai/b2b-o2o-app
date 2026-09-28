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
}
