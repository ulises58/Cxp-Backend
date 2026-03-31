<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantPermissionCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TenantPermissionResource;
use Illuminate\Http\JsonResponse;

class PermissionCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::RolesManage->asMiddleware());
    }

    public function __invoke(TenantPermissionCatalog $catalog): JsonResponse
    {
        $items = $catalog->all();

        return response()->json([
            'data' => TenantPermissionResource::collection($items),
        ]);
    }
}
