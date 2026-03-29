<?php

declare(strict_types=1);

namespace App\Application\Tenant;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class TenantMemberService
{
    public function paginateUsers(int $perPage = 15, ?int $excludeUserId = null): LengthAwarePaginator
    {
        $tenantId = getPermissionsTeamId();

        return User::query()
            ->where('tenant_id', $tenantId)
            ->when(
                $excludeUserId !== null,
                static fn ($q) => $q->where('id', '!=', $excludeUserId),
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findUser(int $id): ?User
    {
        $tenantId = getPermissionsTeamId();

        return User::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    public function syncRoles(User $user, array $roleNames): User
    {
        $tenantId = getPermissionsTeamId();
        if ((string) $user->tenant_id !== (string) $tenantId) {
            abort(404);
        }

        if ($roleNames === []) {
            throw ValidationException::withMessages([
                'roles' => [__('api.roles_required')],
            ]);
        }

        $roles = Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', $tenantId)
            ->whereIn('name', $roleNames)
            ->get();

        if ($roles->count() !== count(array_unique($roleNames))) {
            throw ValidationException::withMessages([
                'roles' => [__('api.tenant_roles_unknown_or_foreign')],
            ]);
        }

        $user->syncRoles(...$roles->pluck('name')->all());
        $user->unsetRelation('roles');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->load('roles');
    }
}
