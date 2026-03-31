<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantScopedUserRepository
{
    public function paginateForTenant(
        int|string $tenantId,
        int $perPage,
        ?int $excludeUserId = null,
    ): LengthAwarePaginator {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->when(
                $excludeUserId !== null,
                static fn ($q) => $q->where('id', '!=', $excludeUserId),
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findInTenant(int $userId, int|string $tenantId): ?User
    {
        return User::query()
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();
    }
}
