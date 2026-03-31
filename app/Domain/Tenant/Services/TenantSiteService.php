<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Repositories\SiteRepository;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantSiteService
{
    public function __construct(
        private readonly SiteRepository $sites,
    ) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->sites->paginateForTenant($this->tenantId(), $perPage);
    }

    public function create(string $name, ?string $description, bool $isActive): Site
    {
        return $this->sites->create([
            'tenant_id' => $this->tenantId(),
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
        ]);
    }

    public function update(Site $site, string $name, ?string $description, bool $isActive): Site
    {
        $this->assertSameTenant($site);

        $site->name = $name;
        $site->description = $description;
        $site->is_active = $isActive;
        $site->save();

        return $site->refresh();
    }

    public function delete(Site $site): void
    {
        $this->assertSameTenant($site);
        $site->delete();
    }

    private function assertSameTenant(Site $site): void
    {
        if (! $this->sites->belongsToTenant($site, $this->tenantId())) {
            abort(404);
        }
    }

    private function tenantId(): string
    {
        return (string) getPermissionsTeamId();
    }
}
