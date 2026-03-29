<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Application\Tenant\TenantRoleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantRoleRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantRoleRequest;
use App\Http\Resources\Api\V1\TenantRoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class TenantRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.manage');
    }

    public function index(TenantRoleService $roles): JsonResponse
    {
        $list = $roles->listRoles()->load('permissions');

        return response()->json([
            'data' => TenantRoleResource::collection($list),
        ]);
    }

    public function show(Role $tenantRole): TenantRoleResource
    {
        $tenantRole->load('permissions');

        return new TenantRoleResource($tenantRole);
    }

    public function store(StoreTenantRoleRequest $request, TenantRoleService $roles): JsonResponse
    {
        $validated = $request->validated();
        $role = $roles->create($validated['name'], $validated['permissions']);

        return (new TenantRoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateTenantRoleRequest $request, Role $tenantRole, TenantRoleService $roles): TenantRoleResource
    {
        $validated = $request->validated();
        $permissionNames = array_key_exists('permissions', $validated)
            ? $validated['permissions']
            : null;
        $name = $validated['name'] ?? null;

        $role = $roles->update($tenantRole, $name, $permissionNames);

        return new TenantRoleResource($role);
    }

    public function destroy(Role $tenantRole, TenantRoleService $roles): Response
    {
        $roles->delete($tenantRole);

        return response()->noContent();
    }
}
