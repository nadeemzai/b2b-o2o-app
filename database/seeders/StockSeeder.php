<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\TownshipStore;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Seed opening stock for all active products in the Lahore store.
     * Qty defaults to 100 units per product (adjust for go-live).
     */
    public function run(): void
    {
        $store    = TownshipStore::where('code', 'TS-LHR-01')->firstOrFail();
        $products = Product::active()->get();

        foreach ($products as $product) {
            StockLevel::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'store_id'   => $store->id,
                ],
                [
                    'qty_on_hand'  => 100,
                    'qty_reserved' => 0,
                ]
            );
        }

        $this->command->info("Stock seeded: {$products->count()} products × 100 units in {$store->name}.");
    }
}
