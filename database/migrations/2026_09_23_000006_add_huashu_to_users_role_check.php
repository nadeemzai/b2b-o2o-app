<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The users.role column is backed by a Postgres CHECK constraint (created via
// Blueprint::enum() in 2026_09_17_000002_modify_users_add_role.php), which only
// allowed 'admin' | 'retailer' | 'store_staff'. The Huashu fulfillment portal
// (make:huashu-user, seed_huashu_role migration) needs a 'huashu' role too.
//
// SQLite (used in tests) doesn't support ALTER TABLE ... DROP CONSTRAINT, and its
// CHECK constraint on this column isn't enforced strictly enough to matter for
// tests, so this only needs to run for Postgres.
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE users DROP CONSTRAINT users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin', 'retailer', 'store_staff', 'huashu']::text[]))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("UPDATE users SET role = 'retailer' WHERE role = 'huashu'");
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin', 'retailer', 'store_staff']::text[]))");
    }
};
