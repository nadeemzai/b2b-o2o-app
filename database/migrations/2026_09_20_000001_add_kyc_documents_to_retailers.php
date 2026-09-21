<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add kyc_documents JSON column to retailers table.
     * Stores file paths: { cnic_front, cnic_back, business_doc }
     * Run after Phase1 migrations.
     */
    public function up(): void
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->json('kyc_documents')->nullable()->after('kyc_rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->dropColumn('kyc_documents');
        });
    }
};
