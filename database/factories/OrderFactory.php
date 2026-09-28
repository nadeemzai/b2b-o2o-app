<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Retailer;
use App\Models\TownshipStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'retailer_id'    => Retailer::factory(),
            'store_id'       => TownshipStore::factory(),
            'status'         => Order::STATUS_PENDING,
            'total_pkr'      => $this->faker->randomFloat(2, 100, 5000),
            'payment_method' => 'cod',
            'collected_pkr'  => null,
            'notes'          => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => Order::STATUS_PENDING]);
    }

    public function preparing(): static
    {
        return $this->state(['status' => Order::STATUS_PREPARING]);
    }

    public function delivered(float $collected = 0.0): static
    {
        return $this->state([
            'status'        => Order::STATUS_DELIVERED,
            'collected_pkr' => $collected,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => Order::STATUS_CANCELLED]);
    }
}
