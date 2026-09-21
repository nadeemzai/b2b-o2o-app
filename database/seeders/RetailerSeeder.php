<?php

namespace Database\Seeders;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RetailerSeeder extends Seeder
{
    private array $retailers = [
        [
            'email'         => 'ahmed.general@retailer.com',
            'name'          => 'Ahmed Khan',
            'business_name' => 'Ahmed General Store',
            'cnic'          => '35202-1234567-1',
            'phone'         => '0321-4567890',
            'address'       => 'Shop 5, Model Town Market, Lahore',
            'kyc_status'    => 'approved',
        ],
        [
            'email'         => 'tariq.traders@retailer.com',
            'name'          => 'Tariq Mahmood',
            'business_name' => 'Tariq Traders',
            'cnic'          => '35202-7654321-9',
            'phone'         => '0333-9876543',
            'address'       => 'Plot 22, DHA Phase 4, Lahore',
            'kyc_status'    => 'approved',
        ],
    ];

    public function run(): void
    {
        $store = TownshipStore::where('code', 'TS-LHR-01')->firstOrFail();

        foreach ($this->retailers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('Retailer@12345'),
                    'role'              => 'retailer',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            Retailer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_id'      => $store->id,
                    'business_name' => $data['business_name'],
                    'cnic'          => $data['cnic'],
                    'phone'         => $data['phone'],
                    'address'       => $data['address'],
                    'kyc_status'    => $data['kyc_status'],
                ]
            );
        }

        $this->command->info('Retailers seeded: 2 approved retailers for Lahore store.');
    }
}
