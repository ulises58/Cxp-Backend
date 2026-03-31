<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Actions\SyncTenantMemberRolesAction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantMemberService
{
    public function __construct(
        private readonly SyncTenantMemberRolesAction $syncTenantMemberRoles,
    ) {}

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
        return ($this->syncTenantMemberRoles)($user, $roleNames);
    }
}
