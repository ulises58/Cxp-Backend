<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Domain\Landlord\Services\LandlordTenantUserService;
use App\Domain\Shared\Enums\CxpPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Landlord\StoreLandlordTenantUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class LandlordTenantUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::TenantUsersViewAny->asMiddleware())->only(['index']);
        $this->middleware(CxpPermission::TenantUsersCreate->asMiddleware())->only(['store']);
        $this->middleware(CxpPermission::middlewareOr(
            CxpPermission::TenantUsersViewAny,
            CxpPermission::TenantUsersCreate,
        ))->only(['roles']);
    }

    public function roles(Tenant $tenant, LandlordTenantUserService $users): JsonResponse
    {
        $roles = $users->roleSummariesForTenant($tenant);

        return response()->json([
            'data' => $roles->map(static fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()->all(),
        ]);
    }

    public function index(Request $request, Tenant $tenant, LandlordTenantUserService $users): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $users->paginateForTenant($tenant, $perPage);
        $page->load(['roles']);

        return UserResource::collection($page)->response();
    }

    public function store(
        StoreLandlordTenantUserRequest $request,
        Tenant $tenant,
        LandlordTenantUserService $users,
    ): JsonResponse {
        $validated = $request->validated();
        $user = $users->create(
            $tenant,
            $validated['name'],
            $validated['email'],
            $validated['password'],
            $validated['roles'],
        );

        return response()->json([
            'data' => new UserResource($user),
        ], Response::HTTP_CREATED);
    }
}
