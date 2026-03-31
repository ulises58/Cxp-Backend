<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Data\Api\V1\TenantRoleData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantRoleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantRoleRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class TenantRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::RolesManage->asMiddleware());
    }

    public function index(TenantRoleService $roles): JsonResponse
    {
        $list = $roles->listRoles()->load('permissions');
        $data = $list->map(static fn (Role $role) => TenantRoleData::fromRole($role)->toArray())->values()->all();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function show(Role $tenantRole): JsonResponse
    {
        $tenantRole->load('permissions');

        return response()->json([
            'data' => TenantRoleData::fromRole($tenantRole)->toArray(),
        ]);
    }

    public function store(StoreTenantRoleRequest $request, TenantRoleService $roles): JsonResponse
    {
        $validated = $request->validated();
        $role = $roles->create($validated['name'], $validated['permissions']);

        return response()->json([
            'data' => TenantRoleData::fromRole($role)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateTenantRoleRequest $request, Role $tenantRole, TenantRoleService $roles): JsonResponse
    {
        $validated = $request->validated();
        $permissionNames = array_key_exists('permissions', $validated)
            ? $validated['permissions']
            : null;
        $name = $validated['name'] ?? null;

        $role = $roles->update($tenantRole, $name, $permissionNames);

        return response()->json([
            'data' => TenantRoleData::fromRole($role)->toArray(),
        ]);
    }

    public function destroy(Role $tenantRole, TenantRoleService $roles): Response
    {
        $roles->delete($tenantRole);

        return response()->noContent();
    }
}
