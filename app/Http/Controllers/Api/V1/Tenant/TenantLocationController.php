<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Data\Api\V1\ApiV1PaginatedResponse;
use App\Domain\Shared\Data\Api\V1\LocationData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantLocationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantLocationRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantLocationRequest;
use App\Models\Location;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::LocationsViewAny->asMiddleware())->only(['index']);
        $this->middleware(CxpPermission::LocationsCreate->asMiddleware())->only(['store']);
        $this->middleware(CxpPermission::LocationsRead->asMiddleware())->only(['show']);
        $this->middleware(CxpPermission::LocationsUpdate->asMiddleware())->only(['update']);
        $this->middleware(CxpPermission::LocationsDelete->asMiddleware())->only(['destroy']);
    }

    public function index(Request $request, Site $tenantSite, TenantLocationService $locations): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $locations->paginateForSite($tenantSite, $perPage);

        return response()->json(
            ApiV1PaginatedResponse::fromPaginator($page, static fn (Location $loc) => LocationData::fromLocation($loc)->toArray()),
        );
    }

    public function store(StoreTenantLocationRequest $request, Site $tenantSite, TenantLocationService $locations): JsonResponse
    {
        $validated = $request->validated();
        $location = $locations->create(
            $tenantSite,
            $validated['name'],
            $validated['description'] ?? null,
            $validated['address'] ?? null,
            ! array_key_exists('latitude', $validated) || $validated['latitude'] === null
                ? null
                : (float) $validated['latitude'],
            ! array_key_exists('longitude', $validated) || $validated['longitude'] === null
                ? null
                : (float) $validated['longitude'],
            ! array_key_exists('radius', $validated) || $validated['radius'] === null
                ? null
                : (float) $validated['radius'],
            $validated['timezone'] ?? null,
            $validated['metadata'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return response()->json([
            'data' => LocationData::fromLocation($location)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Site $tenantSite, Location $tenantLocation): JsonResponse
    {
        return response()->json([
            'data' => LocationData::fromLocation($tenantLocation)->toArray(),
        ]);
    }

    public function update(
        UpdateTenantLocationRequest $request,
        Site $tenantSite,
        Location $tenantLocation,
        TenantLocationService $locations,
    ): JsonResponse {
        $validated = $request->validated();
        $description = array_key_exists('description', $validated)
            ? $validated['description']
            : $tenantLocation->description;
        $metadata = array_key_exists('metadata', $validated)
            ? $validated['metadata']
            : $tenantLocation->metadata;
        $address = array_key_exists('address', $validated)
            ? $validated['address']
            : $tenantLocation->address;
        $latitude = array_key_exists('latitude', $validated)
            ? ($validated['latitude'] !== null ? (float) $validated['latitude'] : null)
            : $tenantLocation->latitude;
        $longitude = array_key_exists('longitude', $validated)
            ? ($validated['longitude'] !== null ? (float) $validated['longitude'] : null)
            : $tenantLocation->longitude;
        $radius = array_key_exists('radius', $validated)
            ? ($validated['radius'] !== null ? (float) $validated['radius'] : null)
            : $tenantLocation->radius;
        $timezone = array_key_exists('timezone', $validated)
            ? $validated['timezone']
            : $tenantLocation->timezone;

        $location = $locations->update(
            $tenantLocation,
            $validated['name'] ?? $tenantLocation->name,
            $description,
            $address,
            $latitude,
            $longitude,
            $radius,
            $timezone,
            $metadata,
            array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $tenantLocation->is_active,
        );

        return response()->json([
            'data' => LocationData::fromLocation($location)->toArray(),
        ]);
    }

    public function destroy(Site $tenantSite, Location $tenantLocation, TenantLocationService $locations): Response
    {
        $locations->delete($tenantLocation);

        return response()->noContent();
    }
}
