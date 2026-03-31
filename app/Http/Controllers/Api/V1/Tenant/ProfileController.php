<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Data\Api\V1\TenantProfileData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['roles', 'tenant']);

        $payload = TenantProfileData::fromContext(tenant(), $user);

        return response()->json([
            'data' => $payload->toArray(),
        ]);
    }
}
