<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@oztech.com'],
            [
                'name'              => 'OzTech Admin',
                'password'          => Hash::make('Admin@12345'),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user seeded: admin@oztech.com / Admin@12345');
    }
}
