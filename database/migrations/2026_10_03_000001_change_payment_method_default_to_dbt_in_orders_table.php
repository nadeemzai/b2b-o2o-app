<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Payment method changed from COD (Cash on Delivery) to DBT (Direct Bank Transfer).
     * Retailers now upload payment proof which OZ admin verifies before fulfillment.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // SQLite (used in tests) does not support ALTER COLUMN — skip;
        // tests always run with a fresh schema so the column default is
        // whatever the seeder/factory uses, which is already 'dbt'.
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'dbt'");
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'dbt'");
        }

        // Migrate any existing 'cod' rows to 'dbt' (all drivers support this)
        DB::table('orders')->where('payment_method', 'cod')->update(['payment_method' => 'dbt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'cod'");
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cod'");
        }
    }
};
