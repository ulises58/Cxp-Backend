<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Domain\Tenant\TenantBuiltinRoles;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\Permission\Models\Role;

final class TenantRoleData extends Data
{
    /**
     * @param  list<string>|Optional  $permissions
     */
    public function __construct(
        public int|string $id,
        public string $name,
        public string $guard_name,
        public bool $is_builtin,
        public array|Optional $permissions,
    ) {}

    public static function fromRole(Role $role): self
    {
        $permissions = $role->relationLoaded('permissions')
            ? $role->permissions->pluck('name')->sort()->values()->all()
            : Optional::create();

        return new self(
            id: $role->getKey(),
            name: $role->name,
            guard_name: $role->guard_name,
            is_builtin: in_array($role->name, TenantBuiltinRoles::NAMES, true),
            permissions: $permissions,
        );
    }
}
