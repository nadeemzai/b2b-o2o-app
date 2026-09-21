<?php

namespace Database\Seeders;

use App\Models\StoreStaff;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TownshipStoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── Manager user ──────────────────────────────────────────────────
        $manager = User::firstOrCreate(
            ['email' => 'manager@lahore-ts.com'],
            [
                'name'              => 'Lahore Store Manager',
                'password'          => Hash::make('Manager@12345'),
                'role'              => 'store_staff',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ── Operator user ─────────────────────────────────────────────────
        $operator = User::firstOrCreate(
            ['email' => 'operator@lahore-ts.com'],
            [
                'name'              => 'Lahore Store Operator',
                'password'          => Hash::make('Operator@12345'),
                'role'              => 'store_staff',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ── Rider user ────────────────────────────────────────────────────
        $rider = User::firstOrCreate(
            ['email' => 'rider@lahore-ts.com'],
            [
                'name'              => 'Lahore Store Rider',
                'password'          => Hash::make('Rider@12345'),
                'role'              => 'store_staff',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ── Township Store ────────────────────────────────────────────────
        $store = TownshipStore::firstOrCreate(
            ['code' => 'TS-LHR-01'],
            [
                'name'            => 'Lahore Township Store',
                'city'            => 'Lahore',
                'address'         => 'Plot 12, Township Sector B, Lahore',
                'phone'           => '0300-4001234',
                'manager_user_id' => $manager->id,
                'is_active'       => true,
            ]
        );

        // ── Store Staff rows ──────────────────────────────────────────────
        foreach ([
            [$manager->id,  'manager'],
            [$operator->id, 'operator'],
            [$rider->id,    'rider'],
        ] as [$userId, $position]) {
            StoreStaff::firstOrCreate(
                ['user_id' => $userId, 'store_id' => $store->id],
                ['position' => $position, 'is_active' => true]
            );
        }

        $this->command->info("Township Store seeded: {$store->name} (code: {$store->code})");
    }
}
