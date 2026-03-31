<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;

final class LandlordRoleOptionData extends Data
{
    public function __construct(
        public int|string $id,
        public string $name,
    ) {}

    public static function fromRole(Role $role): self
    {
        return new self(
            id: $role->getKey(),
            name: $role->name,
        );
    }
}
