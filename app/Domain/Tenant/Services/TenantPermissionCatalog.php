<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Repositories\TenantPermissionCatalogRepository;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class TenantPermissionCatalog
{
    public function __construct(
        private readonly TenantPermissionCatalogRepository $permissions,
    ) {}

    /**
     * Permisos que un tenant puede asignar a sus roles (catálogo global acotado).
     *
     * @return Collection<int, Permission>
     */
    public function all(): Collection
    {
        return $this->permissions->assignableForTenantRoles();
    }
}
