<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HuashuUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@huashu.com'],
            [
                'name'              => 'Huashu Admin',
                'password'          => Hash::make('Huashu@2024'),
                'role'              => 'huashu',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Huashu user seeded: admin@huashu.com / Huashu@2024');
    }
}
