<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'circle_viewer', 'guard_name' => 'web']);

        foreach (['view-dashboard', 'view-feeder-list', 'view-feeder-detail', 'view-status-logs', 'export-report'] as $perm) {
            $role->givePermissionTo(Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']));
        }

        $user = \App\Models\User::where('email', 'secir@mgvcl.com')->first();
        if ($user) {
            $user->syncRoles(['circle_viewer']);
        }
    }

    public function down(): void
    {
        $user = \App\Models\User::where('email', 'secir@mgvcl.com')->first();
        if ($user) {
            $user->syncRoles(['circle']);
        }

        Role::where('name', 'circle_viewer')->delete();
    }
};
