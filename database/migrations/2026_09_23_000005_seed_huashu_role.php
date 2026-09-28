<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions for Huashu panel
        $huashuPerms = [
            'huashu.view_orders',
            'huashu.set_ref',
            'huashu.mark_fulfilling',
            'huashu.mark_delivered',
        ];

        foreach ($huashuPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'huashu', 'guard_name' => 'web']);
        $role->syncPermissions($huashuPerms);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::where('name', 'huashu')->first()?->delete();

        $perms = ['huashu.view_orders','huashu.set_ref','huashu.mark_fulfilling','huashu.mark_delivered'];
        Permission::whereIn('name', $perms)->delete();
    }
};
