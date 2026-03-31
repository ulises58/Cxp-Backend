<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Support\TenantTeamRoleGuard;
use App\Domain\Tenant\TenantCatalogPermissionResolver;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class UpdateTenantRoleAction
{
    public function __construct(
        private readonly TenantCatalogPermissionResolver $catalogPermissions,
        private readonly TenantTeamRoleGuard $roleGuard,
    ) {}

    /**
     * @param  array<int, string>|null  $permissionNames  null = no cambiar permisos
     */
    public function __invoke(Role $role, ?string $newName, ?array $permissionNames): Role
    {
        $this->roleGuard->assertRoleInCurrentTeam($role);

        if ($newName !== null && $newName !== $role->name) {
            if ($this->roleGuard->isBuiltinRoleName($role->name)) {
                throw ValidationException::withMessages([
                    'name' => [__('api.tenant_builtin_role_rename_forbidden')],
                ]);
            }
            $this->roleGuard->assertCustomRoleName($newName);
            $this->roleGuard->assertNameAvailableInTeam($newName, (int) $role->id);
            $role->name = $newName;
            $role->save();
        }

        if ($permissionNames !== null) {
            $permissions = $this->catalogPermissions->resolve($permissionNames);
            $role->syncPermissions($permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $role->refresh()->load('permissions');
    }
}
