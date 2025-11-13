<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\{Permission, Role};

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsArray = [
            'users' => ['view_any', 'view', 'create', 'update'],
            'patients' => ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'],
            'appointments' => ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'],
            'consultations' => ['view_any', 'view', 'create', 'update'],
            'laboratory_results' => ['view_any', 'view', 'create', 'update'],
            'procedures' => ['view_any', 'view', 'create'],
            'invoices' => ['view_any', 'view', 'create', 'update', 'delete'],
            'invoice_items' => ['create', 'update', 'delete'],
            'payments' => ['create', 'update', 'delete']
        ];

        $permissions = collect($permissionsArray)
            ->flatMap(fn ($actions, $resource) =>
                collect($actions)->map(fn ($action) => [
                    'name' => "$resource:$action",
                    'guard_name' => 'web',
                ])
            )
            ->toArray();

        // Delete permissions removed from the array
        Permission::whereNotIn('name', collect($permissions)->pluck('name'))->delete();

        // And insert new ones...
        Permission::upsert($permissions, ['name', 'guard_name'], ['guard_name']);

        $rolePermissions = [
            'admin' => [
                'users:view_any', 'users:view',
                'patients:view_any', 'patients:view',
                'appointments:view_any', 'appointments:view', 'appointments:update', 'appointments:restore',
                'invoices:view',
            ],
            'system_admin' => [
                'users:view_any', 'users:view', 'users:create', 'users:update',
                'patients:view_any', 'patients:view', 'patients:create', 'patients:update', 'patients:delete', 'patients:restore',
            ],
            'cashier' => [
                'patients:view_any', 'patients:view',
                'appointments:view_any', 'appointments:view',
                'invoices:view_any', 'invoices:view', 'invoices:create',
                'invoice_items:create',
                'payments:create', 'payments:update', 'payments:delete'
            ],
            'doctor' => [
                'patients:view_any', 'patients:view',
                'appointments:view_any', 'appointments:view',
                'consultations:view_any', 'consultations:view', 'consultations:create', 'consultations:update',
                'laboratory_results:view_any', 'laboratory_results:view', 'laboratory_results:create',
                'procedures:view_any', 'procedures:view', 'procedures:create',
                'invoice_items:create', 'invoice_items:update', 'invoice_items:delete'
            ],
            'laboratory_staff' => [
                'patients:view_any', 'patients:view',
                'appointments:view_any', 'appointments:view',
                'laboratory_results:view_any', 'laboratory_results:view', 'laboratory_results:create', 'laboratory_results:update',
                'invoices:view',
            ],
            'patient' => [
                'patients:view',
                'appointments:view_any', 'appointments:view', 'appointments:create', 'appointments:update',
                'invoices:view',
                'consultations:view',
                'laboratory_results:view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            $role->syncPermissions($permissions);
        }
    }
}
