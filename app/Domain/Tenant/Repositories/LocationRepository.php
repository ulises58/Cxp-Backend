<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LocationRepository
{
    public function paginateForSiteAndTenant(
        int $siteId,
        int|string $tenantId,
        int $perPage,
    ): LengthAwarePaginator {
        return Location::query()
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Location
    {
        return Location::query()->create($attributes);
    }

    public function belongsToTenant(Location $location, int|string $tenantId): bool
    {
        return (string) $location->tenant_id === (string) $tenantId;
    }
}
