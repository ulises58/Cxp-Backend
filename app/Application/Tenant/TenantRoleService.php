<?php

declare(strict_types=1);

namespace App\Application\Tenant;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class TenantRoleService
{
    public const BUILTIN_NAMES = [
        'tenant_owner',
        'tenant_support',
        'tenant_admin',
        'tenant_user',
    ];

    /**
     * @return Collection<int, Role>
     */
    public function listRoles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', getPermissionsTeamId())
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    public function create(string $name, array $permissionNames): Role
    {
        $this->assertCustomName($name);
        $this->assertNameAvailableInTeam($name);

        $permissions = $this->resolveCatalogPermissions($permissionNames);

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

    /**
     * @param  array<int, string>|null  $permissionNames  null = no cambiar permisos
     */
    public function update(Role $role, ?string $newName, ?array $permissionNames): Role
    {
        $this->assertRoleInCurrentTeam($role);

        if ($newName !== null && $newName !== $role->name) {
            if ($this->isBuiltin($role->name)) {
                throw ValidationException::withMessages([
                    'name' => [__('api.tenant_builtin_role_rename_forbidden')],
                ]);
            }
            $this->assertCustomName($newName);
            $this->assertNameAvailableInTeam($newName, (int) $role->id);
            $role->name = $newName;
            $role->save();
        }

        if ($permissionNames !== null) {
            $permissions = $this->resolveCatalogPermissions($permissionNames);
            $role->syncPermissions($permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $role->refresh()->load('permissions');
    }

    public function delete(Role $role): void
    {
        $this->assertRoleInCurrentTeam($role);

        if ($this->isBuiltin($role->name)) {
            throw ValidationException::withMessages([
                'role' => [__('api.tenant_builtin_role_delete_forbidden')],
            ]);
        }

        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages([
                'role' => [__('api.tenant_role_has_users')],
            ]);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function assertRoleInCurrentTeam(Role $role): void
    {
        if ((string) $role->tenant_id !== (string) getPermissionsTeamId()) {
            abort(404);
        }
    }

    private function isBuiltin(string $name): bool
    {
        return in_array($name, self::BUILTIN_NAMES, true);
    }

    private function assertCustomName(string $name): void
    {
        if ($this->isBuiltin($name)) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_reserved_role_name')],
            ]);
        }

        if (! preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $name)) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_format')],
            ]);
        }
    }

    private function assertNameAvailableInTeam(string $name, ?int $exceptRoleId = null): void
    {
        $query = Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', getPermissionsTeamId())
            ->where('name', $name);

        if ($exceptRoleId !== null) {
            $query->where('id', '!=', $exceptRoleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_taken')],
            ]);
        }
    }

    /**
     * @param  array<int, string>  $permissionNames
     * @return Collection<int, Permission>
     */
    private function resolveCatalogPermissions(array $permissionNames): Collection
    {
        $catalog = BootstrapTenantDefaultRoles::PERMISSIONS;
        $invalid = array_diff($permissionNames, $catalog);
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'permissions' => [__('api.tenant_permission_not_in_catalog')],
            ]);
        }

        $unique = array_values(array_unique($permissionNames));
        $permissions = Permission::query()
            ->where('guard_name', 'sanctum')
            ->whereIn('name', $unique)
            ->get();

        if ($permissions->count() !== count($unique)) {
            throw ValidationException::withMessages([
                'permissions' => [__('api.tenant_permission_not_in_catalog')],
            ]);
        }

        return $permissions;
    }
}
