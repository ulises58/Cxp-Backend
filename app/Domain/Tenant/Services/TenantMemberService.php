<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Actions\SyncTenantMemberRolesAction;
use App\Domain\Tenant\Repositories\TenantScopedUserRepository;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantMemberService
{
    public function __construct(
        private readonly SyncTenantMemberRolesAction $syncTenantMemberRoles,
        private readonly TenantScopedUserRepository $users,
    ) {}

    public function paginateUsers(int $perPage = 15, ?int $excludeUserId = null): LengthAwarePaginator
    {
        return $this->users->paginateForTenant(
            getPermissionsTeamId(),
            $perPage,
            $excludeUserId,
        );
    }

    public function findUser(int $id): ?User
    {
        return $this->users->findInTenant($id, getPermissionsTeamId());
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    public function syncRoles(User $user, array $roleNames): User
    {
        return ($this->syncTenantMemberRoles)($user, $roleNames);
    }
}
