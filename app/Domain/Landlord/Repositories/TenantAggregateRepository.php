<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Repositories;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantAggregateRepository
{
    public function paginateOrderedBySlug(int $perPage): LengthAwarePaginator
    {
        return Tenant::query()
            ->orderBy('slug')
            ->paginate($perPage);
    }

    public function create(string $slug, ?string $name, bool $isActive): Tenant
    {
        return Tenant::query()->create([
            'slug' => $slug,
            'name' => $name,
            'is_active' => $isActive,
        ]);
    }

    public function save(Tenant $tenant): Tenant
    {
        $tenant->save();

        return $tenant->refresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }
}
