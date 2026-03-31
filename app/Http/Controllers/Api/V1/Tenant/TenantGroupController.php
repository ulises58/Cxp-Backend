<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Data\Api\V1\ApiV1PaginatedResponse;
use App\Domain\Shared\Data\Api\V1\GroupData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantGroupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantGroupRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantGroupRequest;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::GroupsViewAny->asMiddleware())->only(['index']);
        $this->middleware(CxpPermission::GroupsCreate->asMiddleware())->only(['store']);
        $this->middleware(CxpPermission::GroupsRead->asMiddleware())->only(['show']);
        $this->middleware(CxpPermission::GroupsUpdate->asMiddleware())->only(['update']);
        $this->middleware(CxpPermission::GroupsDelete->asMiddleware())->only(['destroy']);
    }

    public function index(Request $request, TenantGroupService $groups): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $groups->paginate($perPage);

        return response()->json(
            ApiV1PaginatedResponse::fromPaginator($page, static fn (Group $g) => GroupData::fromGroup($g)->toArray()),
        );
    }

    public function store(StoreTenantGroupRequest $request, TenantGroupService $groups): JsonResponse
    {
        $validated = $request->validated();
        $group = $groups->create(
            $validated['name'],
            $validated['description'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return response()->json([
            'data' => GroupData::fromGroup($group)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Group $tenantGroup): JsonResponse
    {
        return response()->json([
            'data' => GroupData::fromGroup($tenantGroup)->toArray(),
        ]);
    }

    public function update(UpdateTenantGroupRequest $request, Group $tenantGroup, TenantGroupService $groups): JsonResponse
    {
        $validated = $request->validated();
        $description = array_key_exists('description', $validated)
            ? $validated['description']
            : $tenantGroup->description;

        $group = $groups->update(
            $tenantGroup,
            $validated['name'] ?? $tenantGroup->name,
            $description,
            array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $tenantGroup->is_active,
        );

        return response()->json([
            'data' => GroupData::fromGroup($group)->toArray(),
        ]);
    }

    public function destroy(Group $tenantGroup, TenantGroupService $groups): Response
    {
        $groups->delete($tenantGroup);

        return response()->noContent();
    }
}
