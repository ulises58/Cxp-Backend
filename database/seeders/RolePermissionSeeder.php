<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        $permissionNames = [
            'landlord.panel',
            'tenants.view-any',
            'tenants.create',
            'tenants.read',
            'tenants.update',
            'tenants.delete',
        ];

        foreach ($permissionNames as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        foreach (['super_admin', 'tenant_admin', 'tenant_member'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        $superAdmin = Role::findByName('super_admin', $guard);
        $superAdmin->syncPermissions($permissionNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
