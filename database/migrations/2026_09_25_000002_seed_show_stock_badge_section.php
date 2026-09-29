<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('homepage_sections')->upsert(
            [['key' => 'show_stock_badge', 'label_en' => 'Show In-Stock Badge', 'is_active' => false, 'display_order' => 30]],
            ['key'],
            ['label_en', 'display_order']
        );
    }

    public function down(): void
    {
        DB::table('homepage_sections')->where('key', 'show_stock_badge')->delete();
    }
};
