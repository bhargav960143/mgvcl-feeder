<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $extra = [
            'view-dashboard',
            'view-feeder-detail',
            'view-status-logs',
            'export-report',
        ];

        foreach (['division_manager', 'sub_division_manager'] as $roleName) {
            $role = Role::findByName($roleName);
            foreach ($extra as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName]);
                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $extra = [
            'view-dashboard',
            'view-feeder-detail',
            'view-status-logs',
            'export-report',
        ];

        foreach (['division_manager', 'sub_division_manager'] as $roleName) {
            $role = Role::findByName($roleName);
            $role->revokePermissionTo($extra);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
