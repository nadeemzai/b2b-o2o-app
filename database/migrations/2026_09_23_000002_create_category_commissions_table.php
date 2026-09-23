<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->decimal('commission_rate', 5, 4); // e.g. 0.0800 = 8%
            $table->date('effective_from');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['category_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_commissions');
    }
};
