<?php

namespace Database\Factories;

use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TownshipStoreFactory extends Factory
{
    protected $model = TownshipStore::class;

    public function definition(): array
    {
        $city = $this->faker->randomElement(['Lahore', 'Karachi', 'Islamabad', 'Faisalabad', 'Rawalpindi']);

        return [
            'name'            => $city . ' Township Store',
            'code'            => strtoupper($this->faker->bothify('TS-???##')),
            'city'            => $city,
            'address'         => $this->faker->streetAddress(),
            'phone'           => '0300-' . $this->faker->numerify('#######'),
            'manager_user_id' => null,
            'is_active'       => true,
        ];
    }

    public function withManager(User $user): static
    {
        return $this->state(['manager_user_id' => $user->id]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
