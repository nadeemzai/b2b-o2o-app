<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Rename huashu.mark_delivered → huashu.mark_dispatched
        $old = Permission::where('name', 'huashu.mark_delivered')->where('guard_name', 'web')->first();
        if ($old) {
            $old->update(['name' => 'huashu.mark_dispatched']);
        } else {
            Permission::firstOrCreate(['name' => 'huashu.mark_dispatched', 'guard_name' => 'web']);
        }

        // Add admin.mark_delivered permission
        $adminDelivered = Permission::firstOrCreate(['name' => 'admin.mark_delivered', 'guard_name' => 'web']);

        // Assign to admin role if it exists
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        $adminRole?->givePermissionTo($adminDelivered);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Restore huashu.mark_dispatched → huashu.mark_delivered
        $p = Permission::where('name', 'huashu.mark_dispatched')->where('guard_name', 'web')->first();
        $p?->update(['name' => 'huashu.mark_delivered']);

        Permission::where('name', 'admin.mark_delivered')->where('guard_name', 'web')->delete();
    }
};
