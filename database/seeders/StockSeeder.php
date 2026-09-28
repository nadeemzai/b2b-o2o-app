<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\TownshipStore;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Seed 200 units of opening stock for every active product across all active stores.
     * Safe to re-run — uses firstOrCreate (won't reset existing stock).
     */
    public function run(): void
    {
        $stores   = TownshipStore::where('is_active', true)->get();
        $products = Product::active()->get();

        $seeded = 0;

        foreach ($stores as $store) {
            foreach ($products as $product) {
                StockLevel::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'store_id'   => $store->id,
                    ],
                    [
                        'qty_on_hand'  => 200,
                        'qty_reserved' => 0,
                    ]
                );
                $seeded++;
            }
        }

        $this->command->info("Stock seeded: {$products->count()} products × {$stores->count()} stores = {$seeded} records.");
    }
}
