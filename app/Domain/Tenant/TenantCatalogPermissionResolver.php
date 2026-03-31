<?php

declare(strict_types=1);

namespace App\Domain\Tenant;

use App\Domain\Shared\Enums\CxpPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

final class TenantCatalogPermissionResolver
{
    /**
     * @param  array<int, string>  $permissionNames
     * @return Collection<int, Permission>
     */
    public function resolve(array $permissionNames): Collection
    {
        $catalog = CxpPermission::tenantRoleCatalogValues();
        $invalid = array_diff($permissionNames, $catalog);
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'permissions' => [__('api.tenant_permission_not_in_catalog')],
            ]);
        }

        $unique = array_values(array_unique($permissionNames));
        $permissions = Permission::query()
            ->where('guard_name', 'sanctum')
            ->whereIn('name', $unique)
            ->get();

        if ($permissions->count() !== count($unique)) {
            throw ValidationException::withMessages([
                'permissions' => [__('api.tenant_permission_not_in_catalog')],
            ]);
        }

        return $permissions;
    }
}
