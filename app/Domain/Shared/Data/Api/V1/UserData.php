<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UserData extends Data
{
    /**
     * @param  list<string>|Optional  $roles  Ausente en JSON si no hay relación cargada.
     * @param  list<string>  $permissions
     */
    public function __construct(
        public int|string $id,
        public string $name,
        public string $email,
        public ?string $tenant_id,
        public array $permissions,
        public array|Optional $roles,
    ) {}

    public static function fromUser(User $user): self
    {
        $roles = $user->relationLoaded('roles')
            ? $user->roles->pluck('name')->values()->all()
            : Optional::create();

        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values()->all();

        return new self(
            id: $user->getKey(),
            name: $user->name,
            email: $user->email,
            tenant_id: $user->tenant_id,
            permissions: $permissions,
            roles: $roles,
        );
    }
}
