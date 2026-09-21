<?php

namespace Database\Factories;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetailerFactory extends Factory
{
    protected $model = Retailer::class;

    public function definition(): array
    {
        // Pakistani CNIC format: 00000-0000000-0 (13 digits + 2 dashes = 15 chars)
        $cnic = $this->faker->numerify('#####-#######-#');

        return [
            'user_id'               => User::factory()->retailer(),
            'store_id'              => TownshipStore::factory(),
            'business_name'         => $this->faker->company() . ' General Store',
            'cnic'                  => $cnic,
            'ntn'                   => null,
            'strn'                  => null,
            'phone'                 => '0301-' . $this->faker->numerify('#######'),
            'address'               => $this->faker->streetAddress() . ', ' . $this->faker->city(),
            'kyc_status'            => 'pending',
            'kyc_rejection_reason'  => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(['kyc_status' => 'approved']);
    }

    public function rejected(string $reason = 'Documents unclear.'): static
    {
        return $this->state([
            'kyc_status'           => 'rejected',
            'kyc_rejection_reason' => $reason,
        ]);
    }
}
