<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class AuthenticateUserWithPasswordAction
{
    public function __invoke(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();
        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
