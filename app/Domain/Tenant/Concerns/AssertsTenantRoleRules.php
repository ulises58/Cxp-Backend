<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Concerns;

use App\Domain\Tenant\TenantBuiltinRoles;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

trait AssertsTenantRoleRules
{
    private function assertRoleInCurrentTeam(Role $role): void
    {
        if ((string) $role->tenant_id !== (string) getPermissionsTeamId()) {
            abort(404);
        }
    }

    private function isBuiltinRoleName(string $name): bool
    {
        return in_array($name, TenantBuiltinRoles::NAMES, true);
    }

    private function assertCustomRoleName(string $name): void
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

    private function assertNameAvailableInTeam(string $name, ?int $exceptRoleId = null): void
    {
        $query = Role::query()
            ->where('guard_name', 'sanctum')
            ->where('tenant_id', getPermissionsTeamId())
            ->where('name', $name);

        if ($exceptRoleId !== null) {
            $query->where('id', '!=', $exceptRoleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => [__('api.tenant_role_name_taken')],
            ]);
        }
    }
}
