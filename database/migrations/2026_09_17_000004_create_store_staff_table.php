<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->cascadeOnDelete();
            $table->string('position', 60)->default('operator'); // operator | manager | rider
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'store_id']);
        });

        // Now safe to add the FK on township_stores.manager_user_id
        Schema::table('township_stores', function (Blueprint $table) {
            $table->foreign('manager_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('township_stores', function (Blueprint $table) {
            $table->dropForeign(['manager_user_id']);
        });
        Schema::dropIfExists('store_staff');
    }
};
