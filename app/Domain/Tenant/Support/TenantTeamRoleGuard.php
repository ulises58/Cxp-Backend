<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Support;

use App\Domain\Tenant\Repositories\TenantTeamRoleRepository;
use App\Domain\Tenant\TenantBuiltinRoles;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class TenantTeamRoleGuard
{
    public function __construct(
        private readonly TenantTeamRoleRepository $roles,
    ) {}

    public function assertRoleInCurrentTeam(Role $role): void
    {
        if ((string) $role->tenant_id !== (string) getPermissionsTeamId()) {
            abort(404);
        }
    }

    public function isBuiltinRoleName(string $name): bool
    {
        return in_array($name, TenantBuiltinRoles::NAMES, true);
    }

    public function assertCustomRoleName(string $name): void
    {
        if ($this->isBuiltinRoleName($name)) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_reserved_role_name')],
            ]);
        }

        if (! preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $name)) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_format')],
            ]);
        }
    }

    public function assertNameAvailableInTeam(string $name, ?int $exceptRoleId = null): void
    {
        $teamId = getPermissionsTeamId();
        if ($this->roles->nameExistsInTeam($name, $teamId, $exceptRoleId)) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_taken')],
            ]);
        }
    }
}
