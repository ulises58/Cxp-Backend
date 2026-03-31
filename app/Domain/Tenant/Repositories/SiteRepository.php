<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SiteRepository
{
    public function paginateForTenant(int|string $tenantId, int $perPage): LengthAwarePaginator
    {
        return Site::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Site
    {
        return Site::query()->create($attributes);
    }

    public function belongsToTenant(Site $site, int|string $tenantId): bool
    {
        return (string) $site->tenant_id === (string) $tenantId;
    }
}
