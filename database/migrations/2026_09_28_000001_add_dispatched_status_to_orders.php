<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL does not support ALTER TABLE ... MODIFY COLUMN for enums.
        // Drop the existing check constraint, widen to VARCHAR(30), and add new constraint.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN (
                'pending',
                'payment_verified',
                'transferred',
                'fulfilling',
                'dispatched',
                'delivered',
                'cancelled'
            ))");
        }
    }

    public function down(): void
    {
        // Check whether any rows use the 'dispatched' status before rolling back.
        // If so, rolling back would fail the constraint — update them to 'fulfilling'.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("UPDATE orders SET status = 'fulfilling' WHERE status = 'dispatched'");

            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN (
                'pending',
                'payment_verified',
                'transferred',
                'fulfilling',
                'delivered',
                'cancelled'
            ))");
        }
    }
};
