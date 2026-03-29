<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Tenant\BootstrapTenantDefaultRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        $landlordPermissions = [
            'landlord.panel',
            'tenants.view-any',
            'tenants.create',
            'tenants.read',
            'tenants.update',
            'tenants.delete',
            'tenant-users.view-any',
            'tenant-users.create',
        ];

        foreach (array_merge($landlordPermissions, BootstrapTenantDefaultRoles::PERMISSIONS) as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        setPermissionsTeamId(config('permission.platform_team_id'));

        $superAdmin = Role::findOrCreate('super_admin', $guard);
        $superAdmin->syncPermissions(
            Permission::query()->where('guard_name', $guard)->whereIn('name', $landlordPermissions)->get()
        );

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
