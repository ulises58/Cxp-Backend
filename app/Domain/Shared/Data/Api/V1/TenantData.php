<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\Tenant;
use Spatie\LaravelData\Data;

final class TenantData extends Data
{
    public function __construct(
        public string $id,
        public string $slug,
        public ?string $name,
        public bool $is_active,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromTenant(Tenant $tenant): self
    {
        return new self(
            id: (string) $tenant->getKey(),
            slug: $tenant->slug,
            name: $tenant->name,
            is_active: (bool) $tenant->is_active,
            created_at: $tenant->created_at?->toIso8601String(),
            updated_at: $tenant->updated_at?->toIso8601String(),
        );
    }
}
