<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Stock levels per product per store
        // qty_available is a PostgreSQL GENERATED ALWAYS AS computed column
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->cascadeOnDelete();
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->unsignedInteger('qty_reserved')->default(0);
            // qty_available added below via raw SQL (GENERATED ALWAYS AS not in Blueprint)
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['product_id', 'store_id']);
        });

        // Add the GENERATED ALWAYS AS column (PostgreSQL only)
        DB::statement('ALTER TABLE stock_levels ADD COLUMN qty_available INTEGER GENERATED ALWAYS AS (qty_on_hand - qty_reserved) STORED');

        // Stock reservations (holds against qty_reserved)
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->enum('status', ['reserved', 'consumed', 'released'])->default('reserved');
            $table->timestamp('created_at')->useCurrent();
        });

        // Audit trail for every stock change
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['inbound', 'outbound', 'adjustment']);
            $table->integer('qty'); // positive = in, negative = out/adjustment
            $table->string('note')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_levels');
    }
};
