<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_roles pivot table, enabling one user to hold multiple roles.
 *
 * A user can simultaneously be:
 *   admin    → Filament admin panel  (guard: admin)
 *   retailer → Retailer web portal   (guard: retailer)
 *   buyer    → Public buyer portal   (guard: retailer, same as retailer for now)
 *   store_staff, huashu, oz_admin → existing Filament panels
 *
 * The legacy users.role column stays in place for backward-compat with existing
 * Filament queries that call $user->role directly. New role checks use
 * $user->hasPortalRole('retailer') which looks at this pivot.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create the pivot table ──────────────────────────────────────
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('role', 32);  // admin | retailer | buyer | store_staff | huashu | oz_admin
            $table->timestamps();
            $table->unique(['user_id', 'role']);
        });

        // ── 2. Back-fill from existing users.role column ───────────────────
        DB::table('users')->orderBy('id')->each(function ($user) {
            if ($user->role) {
                DB::table('user_roles')->insertOrIgnore([
                    'user_id'    => $user->id,
                    'role'       => $user->role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        // ── 3. Widen the Postgres CHECK on users.role to include 'buyer' ──
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement(
                "ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (
                    role::text = ANY (ARRAY[
                        'admin','retailer','buyer','store_staff','huashu','oz_admin'
                    ]::text[])
                )"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement(
                "ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (
                    role::text = ANY (ARRAY[
                        'admin','retailer','store_staff','huashu','oz_admin'
                    ]::text[])
                )"
            );
        }
    }
};
