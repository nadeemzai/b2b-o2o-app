<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_any_retailer', 'view_retailer', 'approve_retailer', 'reject_retailer',
            'view_any_product', 'view_product', 'create_product', 'update_product', 'delete_product',
            'view_any_category', 'create_category', 'update_category', 'delete_category',
            'view_any_store', 'create_store', 'update_store', 'delete_store',
            'view_any_order', 'view_order', 'confirm_order', 'mark_ready_order', 'dispatch_order', 'deliver_order',
            'view_any_stock', 'record_inbound', 'adjust_stock',
            'view_any_user', 'create_user', 'update_user', 'delete_user',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view_any_order', 'view_order', 'confirm_order', 'mark_ready_order', 'dispatch_order', 'deliver_order',
            'view_any_stock', 'record_inbound', 'adjust_stock',
            'view_any_retailer', 'view_retailer',
        ]);

        // Operator
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'view_any_order', 'view_order', 'confirm_order', 'mark_ready_order', 'dispatch_order',
            'view_any_stock', 'record_inbound',
        ]);

        // Rider
        $rider = Role::firstOrCreate(['name' => 'rider', 'guard_name' => 'web']);
        $rider->syncPermissions(['view_any_order', 'view_order', 'dispatch_order', 'deliver_order']);

        // Assign Spatie roles to seeded users
        User::where('role', 'admin')->each(fn ($u) => $u->assignRole('admin'));
        User::where('role', 'store_staff')->each(fn ($u) => $u->assignRole('manager'));
    }
}
