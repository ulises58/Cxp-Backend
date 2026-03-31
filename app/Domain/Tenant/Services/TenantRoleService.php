<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Actions\CreateTenantRoleAction;
use App\Domain\Tenant\Actions\DeleteTenantRoleAction;
use App\Domain\Tenant\Actions\UpdateTenantRoleAction;
use App\Domain\Tenant\Repositories\TenantTeamRoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final class TenantRoleService
{
    public function __construct(
        private readonly CreateTenantRoleAction $createRole,
        private readonly UpdateTenantRoleAction $updateRole,
        private readonly DeleteTenantRoleAction $deleteRole,
        private readonly TenantTeamRoleRepository $tenantRoles,
    ) {}

    /**
     * @return Collection<int, Role>
     */
    public function listRoles(): Collection
    {
        return $this->tenantRoles->listOrderedForTeam(getPermissionsTeamId());
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    public function create(string $name, array $permissionNames): Role
    {
        return ($this->createRole)($name, $permissionNames);
    }

    /**
     * @param  array<int, string>|null  $permissionNames
     */
    public function update(Role $role, ?string $newName, ?array $permissionNames): Role
    {
        return ($this->updateRole)($role, $newName, $permissionNames);
    }

    public function delete(Role $role): void
    {
        ($this->deleteRole)($role);
    }
}
