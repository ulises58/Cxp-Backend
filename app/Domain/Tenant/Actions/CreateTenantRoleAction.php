<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Support\TenantTeamRoleGuard;
use App\Domain\Tenant\TenantCatalogPermissionResolver;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class CreateTenantRoleAction
{
    public function __construct(
        private readonly TenantCatalogPermissionResolver $catalogPermissions,
        private readonly TenantTeamRoleGuard $roleGuard,
    ) {}

    /**
     * @param  array<int, string>  $permissionNames
     */
    public function __invoke(string $name, array $permissionNames): Role
    {
        $this->roleGuard->assertCustomRoleName($name);
        $this->roleGuard->assertNameAvailableInTeam($name);

        $permissions = $this->catalogPermissions->resolve($permissionNames);

        try {
            $role = Role::create([
                'name' => $name,
                'guard_name' => 'sanctum',
            ]);
        } catch (RoleAlreadyExists) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_taken')],
            ]);
        }
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->refresh()->load('permissions');
    }
}
