<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\User;
use Spatie\LaravelData\Data;

final class LandlordHealthUserData extends Data
{
    public function __construct(
        public int|string|null $id,
        public ?string $email,
    ) {}

    public static function fromUser(?User $user): self
    {
        return new self(
            id: $user?->getKey(),
            email: $user?->email,
        );
    }
}
