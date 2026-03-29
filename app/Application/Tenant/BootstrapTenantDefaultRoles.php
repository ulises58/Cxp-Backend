<?php

declare(strict_types=1);

namespace App\Application\Tenant;

use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea roles por tenant (Spatie teams) y asigna permisos del catálogo global.
 */
final class BootstrapTenantDefaultRoles
{
    public const PERMISSIONS = [
        'access',
        'users.view-any',
        'users.invite',
        'users.remove',
        'roles.manage',
        'settings.manage',
    ];

    public static function run(Tenant $tenant): void
    {
        $guard = 'sanctum';
        setPermissionsTeamId($tenant->getKey());

        $all = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', self::PERMISSIONS)
            ->get();

        $owner = Role::create(['name' => 'tenant_owner', 'guard_name' => $guard]);
        $owner->syncPermissions($all);

        $support = Role::create(['name' => 'tenant_support', 'guard_name' => $guard]);
        $support->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', ['access', 'users.view-any'])
                ->get()
        );

        $admin = Role::create(['name' => 'tenant_admin', 'guard_name' => $guard]);
        $admin->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', [
                    'access',
                    'users.view-any',
                    'users.invite',
                    'users.remove',
                ])
                ->get()
        );

        $user = Role::create(['name' => 'tenant_user', 'guard_name' => $guard]);
        $user->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', ['access'])
                ->get()
        );

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
