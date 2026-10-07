<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_city', 100)->nullable()->after('notes');
            $table->string('order_area', 150)->nullable()->after('order_city');
            $table->decimal('order_latitude', 10, 7)->nullable()->after('order_area');
            $table->decimal('order_longitude', 10, 7)->nullable()->after('order_latitude');
            $table->enum('device_type', ['web', 'mobile_app', 'unknown'])
                  ->default('unknown')->after('order_longitude');
            $table->string('user_agent', 500)->nullable()->after('device_type');
            $table->string('ip_address', 45)->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_city',
                'order_area',
                'order_latitude',
                'order_longitude',
                'device_type',
                'user_agent',
                'ip_address',
            ]);
        });
    }
};
