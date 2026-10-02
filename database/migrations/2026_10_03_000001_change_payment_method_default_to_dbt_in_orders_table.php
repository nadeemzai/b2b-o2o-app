<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        // Change column default from 'cod' to 'dbt'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'dbt'");

        // Update any existing 'cod' records to 'dbt'
        DB::table('orders')->where('payment_method', 'cod')->update(['payment_method' => 'dbt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cod'");

        // Note: We do NOT revert 'dbt' → 'cod' on rollback as that could
        // incorrectly classify records created under the new DBT workflow.
    }
};
