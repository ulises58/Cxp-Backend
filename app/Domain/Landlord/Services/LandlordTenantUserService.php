<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Services;

use App\Domain\Landlord\Actions\ProvisionLandlordTenantUserAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LandlordTenantUserService
{
    public function __construct(
        private readonly ProvisionLandlordTenantUserAction $provisionUser,
    ) {}

    public function paginateForTenant(Tenant $tenant, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    public function create(Tenant $tenant, string $name, string $email, string $password, array $roleNames): User
    {
        return ($this->provisionUser)($tenant, $name, $email, $password, $roleNames);
    }
}
