<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);          // e.g. "Color", "Fabric", "Size"
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_type_id')
                  ->constrained('product_variant_types')
                  ->cascadeOnDelete();
            $table->string('value', 100);        // e.g. "Red", "Cotton"
            $table->string('sku_suffix', 30)->nullable();
            $table->decimal('price_adjustment_pkr', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
        Schema::dropIfExists('product_variant_types');
    }
};
