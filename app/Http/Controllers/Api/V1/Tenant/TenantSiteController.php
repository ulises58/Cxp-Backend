<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantSiteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tenant\StoreTenantSiteRequest;
use App\Http\Requests\Api\V1\Tenant\UpdateTenantSiteRequest;
use App\Http\Resources\Api\V1\SiteResource;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantSiteController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::SitesViewAny->asMiddleware())->only(['index']);
        $this->middleware(CxpPermission::SitesCreate->asMiddleware())->only(['store']);
        $this->middleware(CxpPermission::SitesRead->asMiddleware())->only(['show']);
        $this->middleware(CxpPermission::SitesUpdate->asMiddleware())->only(['update']);
        $this->middleware(CxpPermission::SitesDelete->asMiddleware())->only(['destroy']);
    }

    public function index(Request $request, TenantSiteService $sites): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $page = $sites->paginate($perPage);

        return SiteResource::collection($page)->response();
    }

    public function store(StoreTenantSiteRequest $request, TenantSiteService $sites): JsonResponse
    {
        $validated = $request->validated();
        $site = $sites->create(
            $validated['name'],
            $validated['description'] ?? null,
            (bool) ($validated['is_active'] ?? true),
        );

        return (new SiteResource($site))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Site $tenantSite): SiteResource
    {
        return new SiteResource($tenantSite);
    }

    public function update(UpdateTenantSiteRequest $request, Site $tenantSite, TenantSiteService $sites): SiteResource
    {
        $validated = $request->validated();
        $description = array_key_exists('description', $validated)
            ? $validated['description']
            : $tenantSite->description;

        $site = $sites->update(
            $tenantSite,
            $validated['name'] ?? $tenantSite->name,
            $description,
            array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $tenantSite->is_active,
        );

        return new SiteResource($site);
    }

    public function destroy(Site $tenantSite, TenantSiteService $sites): Response
    {
        $sites->delete($tenantSite);

        return response()->noContent();
    }
}
