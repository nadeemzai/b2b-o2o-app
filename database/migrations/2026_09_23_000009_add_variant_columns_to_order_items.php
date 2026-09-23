<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_option_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('product_variant_options')
                  ->nullOnDelete();

            $table->string('variant_label', 200)
                  ->nullable()
                  ->after('variant_option_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_option_id']);
            $table->dropColumn(['variant_option_id', 'variant_label']);
        });
    }
};
