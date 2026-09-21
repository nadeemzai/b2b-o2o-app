<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories (self-referencing for sub-categories)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products (master SKU catalogue — store-agnostic)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 60)->unique();
            $table->string('name_en');                        // English name
            $table->string('name_ur')->nullable();            // Urdu name
            $table->text('description_en')->nullable();
            $table->text('description_ur')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('unit', 30)->default('pcs');       // pcs | box | kg | litre
            $table->unsignedSmallInteger('pieces_per_carton')->default(1);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Product ↔ Category (many-to-many)
        Schema::create('product_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['product_id', 'category_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        // Per-store pricing (product is only orderable where it has a price row)
        Schema::create('product_store_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->cascadeOnDelete();
            $table->decimal('price_pkr', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_store_prices');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
