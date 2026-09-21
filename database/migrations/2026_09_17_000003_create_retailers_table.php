<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retailers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('township_stores')->restrictOnDelete();
            $table->string('business_name');
            $table->string('cnic', 15)->unique();           // 13 digits + dashes
            $table->string('ntn', 20)->nullable()->unique();
            $table->string('strn', 20)->nullable()->unique();
            $table->string('phone', 20);
            $table->text('address');
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('kyc_rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retailers');
    }
};
