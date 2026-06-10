<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['division_manager', 'sub_division_manager'] as $roleName) {
            $role = Role::findByName($roleName);
            if (! $role->hasPermissionTo('update-feeder-status')) {
                $role->givePermissionTo('update-feeder-status');
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['division_manager', 'sub_division_manager'] as $roleName) {
            $role = Role::findByName($roleName);
            if ($role->hasPermissionTo('update-feeder-status')) {
                $role->revokePermissionTo('update-feeder-status');
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
