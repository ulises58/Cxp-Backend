<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final class TenantTeamRoleRepository
{
    /**
     * @return Collection<int, Role>
     */
    public function listOrderedForTeam(int|string $teamId): Collection
    {
        return Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', $teamId)
            ->orderBy('name')
            ->get();
    }

    public function nameExistsInTeam(string $name, int|string $teamId, ?int $exceptRoleId = null): bool
    {
        $query = Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', $teamId)
            ->where('name', $name);

        if ($exceptRoleId !== null) {
            $query->where('id', '!=', $exceptRoleId);
        }

        return $query->exists();
    }

    /**
     * @param  array<int, string>  $names
     * @return Collection<int, Role>
     */
    public function allNamedForTeam(array $names, int|string $teamId): Collection
    {
        return Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', $teamId)
            ->whereIn('name', $names)
            ->get();
    }

    /**
     * @return Collection<int, Role>
     */
    public function listSummaryForTenant(int|string $tenantId): Collection
    {
        return Role::query()
            ->where('tenant_id', $tenantId)
            ->where('guard_name', 'sanctum')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
