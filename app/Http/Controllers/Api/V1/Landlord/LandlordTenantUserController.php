<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Domain\Landlord\Services\LandlordTenantUserService;
use App\Domain\Shared\Data\Api\V1\ApiV1PaginatedResponse;
use App\Domain\Shared\Data\Api\V1\LandlordRoleOptionData;
use App\Domain\Shared\Data\Api\V1\UserData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Landlord\StoreLandlordTenantUserRequest;
use App\Models\Tenant;
use App\Models\User;
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
        $data = $roles->map(static fn (Role $role) => LandlordRoleOptionData::fromRole($role)->toArray())->values()->all();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function index(Request $request, Tenant $tenant, LandlordTenantUserService $users): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $users->paginateForTenant($tenant, $perPage);
        $page->load(['roles']);

        return response()->json(
            ApiV1PaginatedResponse::fromPaginator($page, static fn (User $user) => UserData::fromUser($user)->toArray()),
        );
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
            'data' => UserData::fromUser($user)->toArray(),
        ], Response::HTTP_CREATED);
    }
}
