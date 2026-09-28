<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Huashu's FOB base price — source of truth set by Huashu supplier
            $table->decimal('huashu_base_price_pkr', 12, 2)->nullable()->after('pieces_per_carton');
            // Minimum order quantity — set by Huashu, enforced at checkout
            $table->unsignedInteger('moq')->default(1)->after('huashu_base_price_pkr');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['huashu_base_price_pkr', 'moq']);
        });
    }
};
