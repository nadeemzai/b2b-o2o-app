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
        // PostgreSQL syntax: ALTER COLUMN … SET DEFAULT
        DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'dbt'");

        // Migrate any existing 'cod' rows to 'dbt'
        DB::table('orders')->where('payment_method', 'cod')->update(['payment_method' => 'dbt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'cod'");
    }
};
