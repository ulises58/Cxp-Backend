<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Auth;

use App\Domain\Shared\Data\Api\V1\TenantData;
use App\Domain\Shared\Data\Api\V1\UserData;
use Spatie\LaravelData\Data;

final class LoginSuccessData extends Data
{
    public function __construct(
        public string $token,
        public string $token_type,
        public UserData $user,
        public ?TenantData $tenant,
        public string $context,
    ) {}
}
