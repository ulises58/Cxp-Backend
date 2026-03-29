<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Landlord\HealthController;
use App\Http\Controllers\Api\V1\Landlord\TenantController;
use App\Http\Controllers\Api\V1\Tenant\PermissionCatalogController;
use App\Http\Controllers\Api\V1\Tenant\ProfileController;
use App\Http\Controllers\Api\V1\Tenant\TenantRoleController;
use App\Http\Controllers\Api\V1\Tenant\TenantUserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', LoginController::class)->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'permission.team'])->group(function (): void {
    Route::prefix('landlord')->middleware('landlord')->group(function (): void {
        Route::get('health', HealthController::class);
        Route::apiResource('tenants', TenantController::class);
    });

    Route::middleware('tenant.context')->group(function (): void {
        Route::get('profile', ProfileController::class);

        Route::get('permissions', PermissionCatalogController::class);

        Route::get('roles', [TenantRoleController::class, 'index']);
        Route::post('roles', [TenantRoleController::class, 'store']);
        Route::get('roles/{tenantRole}', [TenantRoleController::class, 'show']);
        Route::patch('roles/{tenantRole}', [TenantRoleController::class, 'update']);
        Route::delete('roles/{tenantRole}', [TenantRoleController::class, 'destroy']);

        Route::get('users', [TenantUserController::class, 'index']);
        Route::get('users/{tenantUser}', [TenantUserController::class, 'show']);
        Route::patch('users/{tenantUser}/roles', [TenantUserController::class, 'syncRoles']);
    });
});
