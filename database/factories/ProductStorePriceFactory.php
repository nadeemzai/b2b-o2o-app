<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductStorePrice;
use App\Models\TownshipStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductStorePrice>
 */
class ProductStorePriceFactory extends Factory
{
    protected $model = ProductStorePrice::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_id'   => TownshipStore::factory(),
            'price_pkr'  => $this->faker->randomFloat(2, 50, 2000),
            'is_active'  => true,
        ];
    }

    /**
     * Mark the price as inactive (product not available at this store).
     */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
