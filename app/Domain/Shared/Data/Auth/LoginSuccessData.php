<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Auth;

use Spatie\LaravelData\Data;

final class LoginSuccessData extends Data
{
    /**
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>|null  $tenant
     */
    public function __construct(
        public string $token,
        public string $token_type,
        public array $user,
        public ?array $tenant,
        public string $context,
    ) {}
}
