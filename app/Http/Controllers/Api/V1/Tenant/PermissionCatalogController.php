<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Shared\Data\Api\V1\TenantPermissionData;
use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\Services\TenantPermissionCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware(CxpPermission::RolesManage->asMiddleware());
    }

    public function __invoke(TenantPermissionCatalog $catalog): JsonResponse
    {
        $items = $catalog->all()->map(
            static fn (Permission $permission) => TenantPermissionData::fromPermission($permission)->toArray(),
        )->values()->all();

        return response()->json([
            'data' => $items,
        ]);
    }
}
