<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\Site;
use Spatie\LaravelData\Data;

final class SiteData extends Data
{
    /**
     * @param  array{id: string, name: string}|null  $group
     */
    public function __construct(
        public string $id,
        public string $tenant_id,
        public ?string $group_id,
        public string $name,
        public ?string $description,
        public bool $is_active,
        public ?array $group,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromSite(Site $site): self
    {
        $site->loadMissing('group');

        $group = $site->group;

        return new self(
            id: (string) $site->getKey(),
            tenant_id: (string) $site->tenant_id,
            group_id: $site->group_id !== null ? (string) $site->group_id : null,
            name: $site->name,
            description: $site->description,
            is_active: (bool) $site->is_active,
            group: $group !== null
                ? ['id' => (string) $group->getKey(), 'name' => $group->name]
                : null,
            created_at: $site->created_at?->toIso8601String(),
            updated_at: $site->updated_at?->toIso8601String(),
        );
    }
}
