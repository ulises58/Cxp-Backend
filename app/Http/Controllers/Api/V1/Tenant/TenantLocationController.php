<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Application\Tenant\TenantLocationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantLocationRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantLocationRequest;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:locations.view-any')->only(['index']);
        $this->middleware('permission:locations.create')->only(['store']);
        $this->middleware('permission:locations.read')->only(['show']);
        $this->middleware('permission:locations.update')->only(['update']);
        $this->middleware('permission:locations.delete')->only(['destroy']);
    }

    public function index(Request $request, Site $tenantSite, TenantLocationService $locations): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $locations->paginateForSite($tenantSite, $perPage);

        return LocationResource::collection($page)->response();
    }

    public function store(StoreTenantLocationRequest $request, Site $tenantSite, TenantLocationService $locations): JsonResponse
    {
        $validated = $request->validated();
        $location = $locations->create(
            $tenantSite,
            $validated['name'],
            $validated['description'] ?? null,
            $validated['metadata'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return (new LocationResource($location))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Site $tenantSite, Location $tenantLocation): LocationResource
    {
        return new LocationResource($tenantLocation);
    }

    public function update(
        UpdateTenantLocationRequest $request,
        Site $tenantSite,
        Location $tenantLocation,
        TenantLocationService $locations,
    ): LocationResource {
        $validated = $request->validated();
        $description = array_key_exists('description', $validated)
            ? $validated['description']
            : $tenantLocation->description;
        $metadata = array_key_exists('metadata', $validated)
            ? $validated['metadata']
            : $tenantLocation->metadata;

        $location = $locations->update(
            $tenantLocation,
            $validated['name'] ?? $tenantLocation->name,
            $description,
            $metadata,
            array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $tenantLocation->is_active,
        );

        return new LocationResource($location);
    }

    public function destroy(Site $tenantSite, Location $tenantLocation, TenantLocationService $locations): Response
    {
        $locations->delete($tenantLocation);

        return response()->noContent();
    }
}
