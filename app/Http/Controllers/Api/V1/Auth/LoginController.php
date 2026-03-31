<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\AuthenticateUserWithPasswordAction;
use App\Domain\Shared\Data\Auth\LoginSuccessData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\TenantResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUserWithPasswordAction $authenticate): JsonResponse
    {
        $user = $authenticate($request->validated('email'), $request->validated('password'));
        if ($user === null) {
            return response()->json([
                'message' => __('auth.failed'),
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($user->tenant_id === null) {
            setPermissionsTeamId(config('permission.platform_team_id'));
        } else {
            setPermissionsTeamId($user->tenant_id);
        }

        $user->load(['roles', 'tenant']);

        if ($user->tenant_id !== null && $user->tenant !== null && ! $user->tenant->is_active) {
            return response()->json([
                'message' => __('api.tenant_inactive'),
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('api')->plainTextToken;

        $payload = LoginSuccessData::from([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve(),
            'tenant' => $user->tenant_id
                ? (new TenantResource($user->tenant))->resolve()
                : null,
            'context' => $user->isLandlordStaff() ? 'landlord' : 'tenant',
        ]);

        return response()->json([
            'data' => $payload->toArray(),
        ]);
    }
}
