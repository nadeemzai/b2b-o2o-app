<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_deal flag to products
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_deal')->default(false)->after('is_active');
        });

        // Homepage sections settings table
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();          // 'deals' | 'new_arrivals'
            $table->string('label_en', 100);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        // Seed the two rows
        DB::table('homepage_sections')->insert([
            ['key' => 'deals',        'label_en' => 'Sourcing Top Deals', 'is_active' => true, 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'new_arrivals', 'label_en' => 'New Arrivals',        'is_active' => true, 'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_deal');
        });
        Schema::dropIfExists('homepage_sections');
    }
};
