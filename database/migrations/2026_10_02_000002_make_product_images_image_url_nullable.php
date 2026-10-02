<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow image_url to be NULL so that Filament FileUpload inside a Repeater
     * can save a row without triggering a NOT NULL violation when no new file
     * is uploaded during an edit. The Filament resource already uses
     * ->dehydrated(fn ($state) => filled($state)) as the primary guard;
     * this migration is belt-and-suspenders.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('image_url')->nullable(false)->change();
        });
    }
};
