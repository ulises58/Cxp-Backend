<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use App\Models\User;
use Spatie\LaravelData\Data;

final class LandlordHealthData extends Data
{
    public function __construct(
        public string $scope,
        public LandlordHealthUserData $user,
    ) {}

    public static function fromRequestUser(?User $user): self
    {
        return new self(
            scope: 'landlord',
            user: LandlordHealthUserData::fromUser($user),
        );
    }
}
