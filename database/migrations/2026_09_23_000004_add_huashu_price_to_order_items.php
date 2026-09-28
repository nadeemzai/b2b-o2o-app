<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshot of Huashu's base price at order creation (for commission audit)
            $table->decimal('huashu_unit_price_pkr', 12, 2)->nullable()->after('unit_price_pkr');
            // Snapshot of commission rate at order creation
            $table->decimal('commission_rate', 5, 4)->nullable()->after('huashu_unit_price_pkr');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['huashu_unit_price_pkr', 'commission_rate']);
        });
    }
};
