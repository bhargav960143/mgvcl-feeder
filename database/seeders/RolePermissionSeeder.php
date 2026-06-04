<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-feeder-list',
            'update-feeder-status',
            'view-dashboard',
            'view-feeder-detail',
            'view-status-logs',
            'export-report',
            'manage-division',
            'manage-sub-division',
            'manage-substation',
            'manage-feeder',
            'manage-circle',
            'manage-users',
            'import-csv',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'admin' => $permissions,

            'circle' => [
                'view-feeder-list',
                'update-feeder-status',
                'view-dashboard',
                'view-feeder-detail',
                'view-status-logs',
                'export-report',
                'manage-division',
                'manage-sub-division',
                'manage-substation',
                'manage-feeder',
            ],

            'division_manager' => [
                'view-feeder-list',
                'update-feeder-status',
            ],

            'sub_division_manager' => [
                'view-feeder-list',
                'update-feeder-status',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
