<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Permission;

final class TenantPermissionData extends Data
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromPermission(Permission $permission): self
    {
        return new self(name: $permission->name);
    }
}
