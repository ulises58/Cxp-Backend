<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Repositories\LocationRepository;
use App\Domain\Tenant\Repositories\SiteRepository;
use App\Models\Location;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantLocationService
{
    public function __construct(
        private readonly LocationRepository $locations,
        private readonly SiteRepository $sites,
    ) {}

    public function paginateForSite(Site $site, int $perPage): LengthAwarePaginator
    {
        $this->assertSameTenant($site);

        return $this->locations->paginateForSiteAndTenant(
            (int) $site->id,
            $this->tenantId(),
            $perPage,
        );
    }

    public function create(
        Site $site,
        string $name,
        ?string $description,
        ?array $metadata,
        bool $isActive,
    ): Location {
        $this->assertSameTenant($site);

        return $this->locations->create([
            'tenant_id' => $this->tenantId(),
            'site_id' => $site->id,
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata,
            'is_active' => $isActive,
        ]);
    }

    public function update(
        Location $location,
        string $name,
        ?string $description,
        ?array $metadata,
        bool $isActive,
    ): Location {
        $this->assertSameTenant($location);

        $location->name = $name;
        $location->description = $description;
        $location->metadata = $metadata;
        $location->is_active = $isActive;
        $location->save();

        return $location->refresh();
    }

    public function delete(Location $location): void
    {
        $this->assertSameTenant($location);
        $location->delete();
    }

    private function assertSameTenant(Site|Location $model): void
    {
        $tenantId = $this->tenantId();
        $ok = $model instanceof Site
            ? $this->sites->belongsToTenant($model, $tenantId)
            : $this->locations->belongsToTenant($model, $tenantId);

        if (! $ok) {
            abort(404);
        }
    }

    private function tenantId(): string
    {
        return (string) getPermissionsTeamId();
    }
}
