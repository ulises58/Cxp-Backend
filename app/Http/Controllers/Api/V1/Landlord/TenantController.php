<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Domain\Landlord\Services\TenantService;
use App\Domain\Shared\Enums\CxpPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Landlord\StoreTenantRequest;
use App\Http\Requests\Api\V1\Landlord\UpdateTenantRequest;
use App\Http\Resources\Api\V1\TenantResource;
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

        return TenantResource::collection($tenants->paginate($perPage))->response();
    }

    public function store(StoreTenantRequest $request, TenantService $tenants): JsonResponse
    {
        $validated = $request->validated();
        $tenant = $tenants->create(
            $validated['slug'],
            $validated['name'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return (new TenantResource($tenant))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant): TenantResource
    {
        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant, TenantService $tenants): TenantResource
    {
        $validated = $request->validated();
        $slug = $validated['slug'] ?? $tenant->slug;
        $name = array_key_exists('name', $validated) ? $validated['name'] : $tenant->name;
        $isActive = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : (bool) $tenant->is_active;

        $tenant = $tenants->update($tenant, $slug, $name, $isActive);

        return new TenantResource($tenant);
    }

    public function destroy(Tenant $tenant, TenantService $tenants): Response
    {
        $tenants->delete($tenant);

        return response()->noContent();
    }
}
