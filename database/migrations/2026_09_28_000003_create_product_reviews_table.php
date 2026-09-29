<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('reviewer_name');
            $table->string('reviewer_location')->nullable(); // e.g. "Lahore, Pakistan"
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('verified_purchase')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
