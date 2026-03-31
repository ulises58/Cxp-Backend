<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Models\Location;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantLocationService
{
    public function paginateForSite(Site $site, int $perPage): LengthAwarePaginator
    {
        $this->assertSameTenant($site);

        return Location::query()
            ->where('site_id', $site->id)
            ->where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(
        Site $site,
        string $name,
        ?string $description,
        ?array $metadata,
        bool $isActive,
    ): Location {
        $this->assertSameTenant($site);

        return Location::query()->create([
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
        if ((string) $model->tenant_id !== (string) $this->tenantId()) {
            abort(404);
        }
    }

    private function tenantId(): string
    {
        $id = getPermissionsTeamId();

        return (string) $id;
    }
}
