<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Services;

use App\Domain\Landlord\Actions\ProvisionLandlordTenantUserAction;
use App\Domain\Tenant\Repositories\TenantScopedUserRepository;
use App\Domain\Tenant\Repositories\TenantTeamRoleRepository;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final class LandlordTenantUserService
{
    public function __construct(
        private readonly ProvisionLandlordTenantUserAction $provisionUser,
        private readonly TenantScopedUserRepository $users,
        private readonly TenantTeamRoleRepository $tenantRoles,
    ) {}

    public function paginateForTenant(Tenant $tenant, int $perPage): LengthAwarePaginator
    {
        return $this->users->paginateForTenant($tenant->id, $perPage);
    }

    /**
     * Roles del tenant para selects (landlord).
     *
     * @return Collection<int, Role>
     */
    public function roleSummariesForTenant(Tenant $tenant): Collection
    {
        return $this->tenantRoles->listSummaryForTenant($tenant->id);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    public function create(Tenant $tenant, string $name, string $email, string $password, array $roleNames): User
    {
        return ($this->provisionUser)($tenant, $name, $email, $password, $roleNames);
    }
}
