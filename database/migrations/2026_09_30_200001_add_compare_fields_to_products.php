<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('origin', 100)->nullable()->after('moq');
            $table->decimal('weight_g', 8, 2)->nullable()->after('origin')
                  ->comment('Weight in grams per unit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['origin', 'weight_g']);
        });
    }
};
