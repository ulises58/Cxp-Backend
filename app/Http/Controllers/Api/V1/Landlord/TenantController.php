<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Domain\Landlord\Services\TenantService;
use App\Domain\Shared\Data\Api\V1\ApiV1PaginatedResponse;
use App\Domain\Shared\Data\Api\V1\TenantData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Landlord\StoreTenantRequest;
use App\Http\Requests\Api\V1\Landlord\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::TenantsViewAny->asMiddleware())->only(['index']);
        $this->middleware(CxpPermission::TenantsCreate->asMiddleware())->only(['store']);
        $this->middleware(CxpPermission::TenantsRead->asMiddleware())->only(['show']);
        $this->middleware(CxpPermission::TenantsUpdate->asMiddleware())->only(['update']);
        $this->middleware(CxpPermission::TenantsDelete->asMiddleware())->only(['destroy']);
    }

    public function index(Request $request, TenantService $tenants): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $tenants->paginate($perPage);

        return response()->json(
            ApiV1PaginatedResponse::fromPaginator($page, static fn (Tenant $t) => TenantData::fromTenant($t)->toArray()),
        );
    }

    public function store(StoreTenantRequest $request, TenantService $tenants): JsonResponse
    {
        $validated = $request->validated();
        $tenant = $tenants->create(
            $validated['slug'],
            $validated['name'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return response()->json([
            'data' => TenantData::fromTenant($tenant)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'data' => TenantData::fromTenant($tenant)->toArray(),
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant, TenantService $tenants): JsonResponse
    {
        $validated = $request->validated();
        $slug = $validated['slug'] ?? $tenant->slug;
        $name = array_key_exists('name', $validated) ? $validated['name'] : $tenant->name;
        $isActive = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : (bool) $tenant->is_active;

        $tenant = $tenants->update($tenant, $slug, $name, $isActive);

        return response()->json([
            'data' => TenantData::fromTenant($tenant)->toArray(),
        ]);
    }

    public function destroy(Tenant $tenant, TenantService $tenants): Response
    {
        $tenants->delete($tenant);

        return response()->noContent();
    }
}
