<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_id')->constrained('retailers')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->restrictOnDelete();
            $table->enum('status', [
                'pending',
                'preparing',
                'ready_for_delivery',
                'delivered',
                'cancelled',
            ])->default('pending');
            $table->decimal('total_pkr', 12, 2);        // frozen at creation
            $table->string('payment_method', 20)->default('cod'); // always cod in phase 1
            $table->decimal('collected_pkr', 12, 2)->nullable();  // filled on /deliver
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);      // store order queue query
            $table->index(['retailer_id', 'created_at']); // retailer history query
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price_pkr', 12, 2);   // price frozen at order time

            // SQLite supports GENERATED ALWAYS AS only in CREATE TABLE, not ALTER TABLE.
            // Include it inline here for SQLite; PostgreSQL uses the ALTER below.
            if (DB::connection()->getDriverName() === 'sqlite') {
                $table->decimal('line_total_pkr', 12, 2)->storedAs('qty * unit_price_pkr');
            }
        });

        // PostgreSQL: add the generated column via ALTER TABLE
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE order_items ADD COLUMN line_total_pkr NUMERIC(12,2) GENERATED ALWAYS AS (qty * unit_price_pkr) STORED');
        }

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable(); // null on first insert
            $table->string('to_status', 30);
            $table->foreignId('changed_by_user_id')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
