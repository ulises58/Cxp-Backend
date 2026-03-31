<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\Tenant;
use App\Models\User;
use Spatie\LaravelData\Data;

final class TenantProfileData extends Data
{
    public function __construct(
        public TenantData $tenant,
        public UserData $user,
    ) {}

    public static function fromContext(Tenant $tenant, User $user): self
    {
        return new self(
            tenant: TenantData::fromTenant($tenant),
            user: UserData::fromUser($user),
        );
    }
}
