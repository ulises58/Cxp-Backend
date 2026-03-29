<?php

declare(strict_types=1);

namespace App\Application\Tenant;

use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantSiteService
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Site::query()
            ->where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(string $name, ?string $description, bool $isActive): Site
    {
        return Site::query()->create([
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
        if ((string) $site->tenant_id !== (string) $this->tenantId()) {
            abort(404);
        }
    }

    private function tenantId(): string
    {
        $id = getPermissionsTeamId();

        return (string) $id;
    }
}
