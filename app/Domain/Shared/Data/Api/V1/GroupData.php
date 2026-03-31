<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\Group;
use Spatie\LaravelData\Data;

final class GroupData extends Data
{
    public function __construct(
        public string $id,
        public string $tenant_id,
        public string $name,
        public ?string $description,
        public bool $is_active,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromGroup(Group $group): self
    {
        return new self(
            id: (string) $group->getKey(),
            tenant_id: (string) $group->tenant_id,
            name: $group->name,
            description: $group->description,
            is_active: (bool) $group->is_active,
            created_at: $group->created_at?->toIso8601String(),
            updated_at: $group->updated_at?->toIso8601String(),
        );
    }
}
