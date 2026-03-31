<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Services;

use App\Domain\Tenant\Actions\BootstrapTenantDefaultRolesAction;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantService
{
    public function __construct(
        private readonly BootstrapTenantDefaultRolesAction $bootstrapDefaultRoles,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::query()
            ->orderBy('slug')
            ->paginate($perPage);
    }

    public function create(string $slug, ?string $name, bool $isActive = true): Tenant
    {
        $tenant = Tenant::query()->create([
            'slug' => $slug,
            'name' => $name,
            'is_active' => $isActive,
        ]);

        ($this->bootstrapDefaultRoles)($tenant);

        return $tenant->refresh();
    }

    public function update(Tenant $tenant, string $slug, ?string $name, bool $isActive): Tenant
    {
        $tenant->slug = $slug;
        $tenant->name = $name;
        $tenant->is_active = $isActive;
        $tenant->save();

        return $tenant->refresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }
}
