<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GroupRepository
{
    public function paginateForTenant(int|string $tenantId, int $perPage): LengthAwarePaginator
    {
        return Group::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Group
    {
        return Group::query()->create($attributes);
    }

    public function belongsToTenant(Group $group, int|string $tenantId): bool
    {
        return (string) $group->tenant_id === (string) $tenantId;
    }
}
