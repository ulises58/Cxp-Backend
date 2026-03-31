<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\Location;
use Spatie\LaravelData\Data;

final class LocationData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $id,
        public string $tenant_id,
        public string $site_id,
        public string $name,
        public ?string $description,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?float $radius,
        public ?string $timezone,
        public ?array $metadata,
        public bool $is_active,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromLocation(Location $location): self
    {
        return new self(
            id: (string) $location->getKey(),
            tenant_id: (string) $location->tenant_id,
            site_id: (string) $location->site_id,
            name: $location->name,
            description: $location->description,
            address: $location->address,
            latitude: $location->latitude !== null ? (float) $location->latitude : null,
            longitude: $location->longitude !== null ? (float) $location->longitude : null,
            radius: $location->radius !== null ? (float) $location->radius : null,
            timezone: $location->timezone,
            metadata: $location->metadata !== null ? (array) $location->metadata : null,
            is_active: (bool) $location->is_active,
            created_at: $location->created_at?->toIso8601String(),
            updated_at: $location->updated_at?->toIso8601String(),
        );
    }
}
