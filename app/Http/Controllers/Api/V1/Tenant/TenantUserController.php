<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantMemberService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\SyncTenantUserRolesRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::UsersViewAny->asMiddleware())->only(['index', 'show']);
        $this->middleware(CxpPermission::UsersInvite->asMiddleware())->only(['syncRoles']);
    }

    public function index(Request $request, TenantMemberService $members): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $members->paginateUsers($perPage, $request->user()->id);
        $page->load(['roles']);

        return UserResource::collection($page)->response();
    }

    public function show(User $tenantUser): UserResource
    {
        $tenantUser->load(['roles']);

        return new UserResource($tenantUser);
    }

    public function syncRoles(SyncTenantUserRolesRequest $request, User $tenantUser, TenantMemberService $members): JsonResponse
    {
        $user = $members->syncRoles($tenantUser, $request->validated('roles'));

        return response()->json([
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
    }
}
