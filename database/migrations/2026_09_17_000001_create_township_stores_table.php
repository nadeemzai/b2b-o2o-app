<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('township_stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();       // e.g. TS-LHR-01
            $table->string('city', 100);
            $table->text('address');
            $table->string('phone', 20)->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable(); // set after users table
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('township_stores');
    }
};
