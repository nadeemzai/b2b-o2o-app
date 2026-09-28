<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Extend status enum (PostgreSQL) ───────────────────────────────
        // PostgreSQL does not support ALTER TABLE ... MODIFY COLUMN for enums.
        // We cast the column through text to append the new values.
        if (DB::connection()->getDriverName() !== 'sqlite') {
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

        Schema::table('orders', function (Blueprint $table) {
            // Payment verification
            $table->timestamp('payment_verified_at')->nullable()->after('notes');
            $table->foreignId('payment_verified_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('payment_verified_at');

            // Huashu transfer
            $table->timestamp('transferred_to_huashu_at')->nullable()->after('payment_verified_by');
            $table->foreignId('transferred_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('transferred_to_huashu_at');

            // OZ commission (computed and stored at transfer time)
            $table->decimal('oz_commission_pkr', 12, 2)->nullable()->after('transferred_by');

            // Huashu's own reference number (filled by Huashu panel)
            $table->string('huashu_ref', 80)->nullable()->after('oz_commission_pkr');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_verified_by');
            $table->dropConstrainedForeignId('transferred_by');
            $table->dropColumn([
                'payment_verified_at',
                'transferred_to_huashu_at',
                'oz_commission_pkr',
                'huashu_ref',
            ]);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN (
                'pending','preparing','ready_for_delivery','delivered','cancelled'
            ))");
        }
    }
};
