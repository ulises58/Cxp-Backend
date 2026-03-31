<?php

declare(strict_types=1);

namespace App\Domain\Landlord\Actions;

use App\Domain\Tenant\Repositories\TenantTeamRoleRepository;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

final class ProvisionLandlordTenantUserAction
{
    public function __construct(
        private readonly TenantTeamRoleRepository $tenantRoles,
    ) {}

    /**
     * @param  array<int, string>  $roleNames
     */
    public function __invoke(
        Tenant $tenant,
        string $name,
        string $email,
        string $password,
        array $roleNames,
    ): User {
        $this->assertRolesBelongToTenant($tenant, $roleNames);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'tenant_id' => $tenant->id,
        ]);

        setPermissionsTeamId($tenant->id);

        try {
            $user->syncRoles(...array_values($roleNames));
            $user->unsetRelation('roles');
        } finally {
            setPermissionsTeamId(null);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $user->load('roles');
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    private function assertRolesBelongToTenant(Tenant $tenant, array $roleNames): void
    {
        if ($roleNames === []) {
            throw ValidationException::withMessages([
                'roles' => [__('api.roles_required')],
            ]);
        }

        $roles = $this->tenantRoles->allNamedForTeam($roleNames, $tenant->id);

        if ($roles->count() !== count(array_unique($roleNames))) {
            throw ValidationException::withMessages([
                'roles' => [__('api.tenant_roles_unknown_or_foreign')],
            ]);
        }
    }
}
