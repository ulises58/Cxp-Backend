<?php

declare(strict_types=1);

namespace App\Application\Landlord;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class LandlordTenantUserService
{
    public function paginateForTenant(Tenant $tenant, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    public function create(Tenant $tenant, string $name, string $email, string $password, array $roleNames): User
    {
        $this->assertRolesBelongToTenant($tenant, $roleNames);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'tenant_id' => $tenant->id,
        ]);

        setPermissionsTeamId($tenant->id);

        try {
            $user->syncRoles(...array_values($roleNames));
            $user->unsetRelation('roles');
        } finally {
            setPermissionsTeamId(null);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $user->load('roles');
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    private function assertRolesBelongToTenant(Tenant $tenant, array $roleNames): void
    {
        if ($roleNames === []) {
            throw ValidationException::withMessages([
                'roles' => [__('api.roles_required')],
            ]);
        }

        $roles = Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', $tenant->id)
            ->whereIn('name', $roleNames)
            ->get();

        if ($roles->count() !== count(array_unique($roleNames))) {
            throw ValidationException::withMessages([
                'roles' => [__('api.tenant_roles_unknown_or_foreign')],
            ]);
        }
    }
}
