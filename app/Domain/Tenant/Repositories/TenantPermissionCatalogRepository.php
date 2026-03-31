<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Domain\Shared\Enums\CxpPermission;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class TenantPermissionCatalogRepository
{
    /**
     * Permisos globales que el catálogo permite asignar a roles de tenant.
     *
     * @return Collection<int, Permission>
     */
    public function assignableForTenantRoles(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'sanctum')
            ->whereIn('name', CxpPermission::tenantRoleCatalogValues())
            ->orderBy('name')
            ->get();
    }
}
