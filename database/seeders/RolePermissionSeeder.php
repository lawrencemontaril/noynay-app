<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'users' => ['view_any', 'view', 'create', 'update', 'delete'],
            'patients' => ['view_any', 'view', 'create', 'update', 'delete'],
            'appointments' => ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'],
            'consultations' => ['view_any', 'view', 'create', 'update', 'delete'],
            'laboratory_results' => ['view_any', 'view', 'create', 'update', 'delete'],
            'invoices' => ['view_any', 'view', 'create', 'update', 'delete'],
            'invoice_items' => ['create', 'update', 'delete'],
            'payments' => ['create', 'update', 'delete']
        ];

        foreach ($permissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "$resource:$action"]);
            }
        }

        $rolePermissions = [
            'admin' => [
                'users:view_any', 'users:view',
                'patients:view_any', 'patients:view',
                'appointments:view_any', 'appointments:view', 'appointments:update', 'appointments:delete', 'appointments:restore',
                'invoices:view_any', 'invoices:view',
            ],
            'system_admin' => [
                'users:view_any', 'users:view', 'users:create', 'users:update', 'users:delete',
                'patients:view_any', 'patients:view', 'patients:create', 'patients:update', 'patients:delete',
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
                'consultations:view_any', 'consultations:view', 'consultations:create', 'consultations:update', 'consultations:delete',
                'laboratory_results:view_any', 'laboratory_results:view', 'laboratory_results:create',
                'invoices:view', 'invoices:update',
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
