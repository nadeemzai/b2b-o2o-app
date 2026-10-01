<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Which admin panel has claimed (locked) this conversation
            $table->enum('claimed_by', ['oz_admin', 'huashu'])
                  ->nullable()
                  ->after('status');

            // The specific user who sent the first reply (auto-claims on first send)
            $table->foreignId('claimed_by_user_id')
                  ->nullable()
                  ->after('claimed_by')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('claimed_at')->nullable()->after('claimed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_user_id']);
            $table->dropColumn(['claimed_by', 'claimed_by_user_id', 'claimed_at']);
        });
    }
};
