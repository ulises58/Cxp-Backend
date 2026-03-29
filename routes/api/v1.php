<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Landlord\HealthController;
use App\Http\Controllers\Api\V1\Landlord\TenantController;
use App\Http\Controllers\Api\V1\Tenant\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', LoginController::class)->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('landlord')->middleware('landlord')->group(function (): void {
        Route::get('health', HealthController::class);
        Route::apiResource('tenants', TenantController::class);
    });

    Route::middleware('tenant.context')->group(function (): void {
        Route::get('profile', ProfileController::class);
    });
});
