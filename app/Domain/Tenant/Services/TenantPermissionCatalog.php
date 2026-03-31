<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Shared\Enums\CxpPermission;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class TenantPermissionCatalog
{
    /**
     * Permisos que un tenant puede asignar a sus roles (catálogo global acotado).
     *
     * @return Collection<int, Permission>
     */
    public function all(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'sanctum')
            ->whereIn('name', CxpPermission::tenantRoleCatalogValues())
            ->orderBy('name')
            ->get();
    }
}
