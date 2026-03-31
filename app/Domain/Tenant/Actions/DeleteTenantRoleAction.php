<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Concerns\AssertsTenantRoleRules;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class DeleteTenantRoleAction
{
    use AssertsTenantRoleRules;

    public function __invoke(Role $role): void
    {
        $this->assertRoleInCurrentTeam($role);

        if ($this->isBuiltinRoleName($role->name)) {
            throw ValidationException::withMessages([
                'role' => [__('api.tenant_builtin_role_delete_forbidden')],
            ]);
        }

        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages([
                'role' => [__('api.tenant_role_has_users')],
            ]);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
