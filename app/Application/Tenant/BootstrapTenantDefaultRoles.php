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

        $owner = Role::create(['name' => 'owner', 'guard_name' => $guard]);
        $owner->syncPermissions($all);

        $admin = Role::create(['name' => 'admin', 'guard_name' => $guard]);
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

        $userRole = Role::create(['name' => 'user', 'guard_name' => $guard]);
        $userRole->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', ['access'])
                ->get()
        );

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
