<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Shared\Enums\CxpPermission;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class BootstrapTenantDefaultRolesAction
{
    public function __invoke(Tenant $tenant): void
    {
        $guard = 'sanctum';
        setPermissionsTeamId($tenant->getKey());

        $catalogValues = array_map(
            fn (CxpPermission $p) => $p->value,
            CxpPermission::tenantRoleCatalog(),
        );

        $all = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', $catalogValues)
            ->get();

        $owner = Role::create(['name' => 'owner', 'guard_name' => $guard]);
        $owner->syncPermissions($all);

        $adminNames = array_map(
            fn (CxpPermission $p) => $p->value,
            CxpPermission::defaultAdminRolePermissions(),
        );

        $admin = Role::create(['name' => 'admin', 'guard_name' => $guard]);
        $admin->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $adminNames)
                ->get()
        );

        $userNames = array_map(
            fn (CxpPermission $p) => $p->value,
            CxpPermission::defaultUserRolePermissions(),
        );

        $userRole = Role::create(['name' => 'user', 'guard_name' => $guard]);
        $userRole->syncPermissions(
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $userNames)
                ->get()
        );

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
