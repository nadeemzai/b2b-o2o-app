<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Currency the retailer transferred in (PKR / USD / CNY)
            $table->string('payment_currency', 3)->default('PKR')->after('payment_proof_path');

            // PKR → foreign rates snapshotted at order-creation time (from Frankfurter).
            // Multiply total_pkr by these to get the foreign equivalent at that moment.
            $table->decimal('fx_usd_rate', 12, 6)->nullable()->after('payment_currency');
            $table->decimal('fx_cny_rate', 12, 6)->nullable()->after('fx_usd_rate');
            $table->timestamp('fx_captured_at')->nullable()->after('fx_cny_rate');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_currency', 'fx_usd_rate', 'fx_cny_rate', 'fx_captured_at']);
        });
    }
};
