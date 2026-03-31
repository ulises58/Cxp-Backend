<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Services;

use App\Domain\Landlord\Repositories\TenantAggregateRepository;
use App\Domain\Tenant\Actions\BootstrapTenantDefaultRolesAction;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantService
{
    public function __construct(
        private readonly BootstrapTenantDefaultRolesAction $bootstrapDefaultRoles,
        private readonly TenantAggregateRepository $tenants,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenants->paginateOrderedBySlug($perPage);
    }

    public function create(string $slug, ?string $name, bool $isActive = true): Tenant
    {
        $tenant = $this->tenants->create($slug, $name, $isActive);

        ($this->bootstrapDefaultRoles)($tenant);

        return $tenant->refresh();
    }

    public function update(Tenant $tenant, string $slug, ?string $name, bool $isActive): Tenant
    {
        $tenant->slug = $slug;
        $tenant->name = $name;
        $tenant->is_active = $isActive;

        return $this->tenants->save($tenant);
    }

    public function delete(Tenant $tenant): void
    {
        $this->tenants->delete($tenant);
    }
}
