<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            TownshipStoreSeeder::class,
            CatalogueSeeder::class,
            RetailerSeeder::class,
            StockSeeder::class,
            RolesAndPermissionsSeeder::class,
            BigCatalogueSeeder::class,
            CategoryChineseNamesSeeder::class,
        ]);
    }
}
