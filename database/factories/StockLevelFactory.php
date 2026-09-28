<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\TownshipStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockLevel>
 */
class StockLevelFactory extends Factory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        return [
            'product_id'   => Product::factory(),
            'store_id'     => TownshipStore::factory(),
            'qty_on_hand'  => $this->faker->numberBetween(10, 200),
            'qty_reserved' => 0,
        ];
    }

    /**
     * Set specific stock quantities.
     */
    public function withStock(int $onHand, int $reserved = 0): static
    {
        return $this->state([
            'qty_on_hand'  => $onHand,
            'qty_reserved' => $reserved,
        ]);
    }

    /**
     * Out-of-stock (qty_on_hand == qty_reserved, so qty_available == 0).
     */
    public function outOfStock(): static
    {
        return $this->state(function () {
            $qty = $this->faker->numberBetween(1, 10);
            return [
                'qty_on_hand'  => $qty,
                'qty_reserved' => $qty,
            ];
        });
    }
}
