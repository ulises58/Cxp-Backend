<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SyncTenantMemberRolesAction
{
    /**
     * @param  array<int, string>  $roleNames
     */
    public function __invoke(User $user, array $roleNames): User
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
