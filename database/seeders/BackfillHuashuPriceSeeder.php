<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStorePrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-time backfill: sets huashu_base_price_pkr on all products that have
 * a product_store_prices row but no huashu_base_price_pkr yet.
 *
 * Uses the lowest available store price as the base (conservative).
 *
 * Run: php artisan db:seed --class=BackfillHuashuPriceSeeder
 */
class BackfillHuashuPriceSeeder extends Seeder
{
    public function run(): void
    {
        $updated = 0;

        Product::whereNull('huashu_base_price_pkr')
            ->with(['storePrices' => fn ($q) => $q->where('is_active', true)->orderBy('price_pkr')])
            ->each(function (Product $product) use (&$updated) {
                $lowestPrice = $product->storePrices->first();

                if ($lowestPrice) {
                    $product->update([
                        'huashu_base_price_pkr' => $lowestPrice->price_pkr,
                    ]);
                    $updated++;
                }
            });

        $this->command->info("BackfillHuashuPriceSeeder: updated {$updated} products with huashu_base_price_pkr.");

        $stillNull = Product::whereNull('huashu_base_price_pkr')->count();
        if ($stillNull > 0) {
            $this->command->warn("  {$stillNull} products still have no price (no store price row exists for them).");
        }
    }
}
