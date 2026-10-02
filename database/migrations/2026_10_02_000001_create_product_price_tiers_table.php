<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Quantity band
            $table->unsignedInteger('min_qty')->default(1)->comment('Inclusive lower bound');
            $table->unsignedInteger('max_qty')->nullable()->comment('Inclusive upper bound — null = unlimited');

            // Retailer-facing price for this band (not the Huashu base price)
            $table->decimal('price_pkr', 12, 2)->comment('Retailer unit price for this quantity band');

            // Optional human-readable label shown in the pricing table
            $table->string('label', 60)->nullable()->comment('e.g. Retail, Wholesale, Bulk');

            $table->timestamps();

            // Enforce one tier-start per product (no duplicate min_qty per product)
            $table->unique(['product_id', 'min_qty']);

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
