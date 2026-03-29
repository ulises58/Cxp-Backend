<?php

namespace App\Providers;

use App\Models\Location;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('tenantRole', function (string $value): Role {
            $tenantId = auth()->user()?->tenant_id;
            abort_if($tenantId === null, 404);

            $role = Role::query()
                ->where('id', $value)
                ->where('tenant_id', $tenantId)
                ->where('guard_name', 'sanctum')
                ->first();
            abort_if($role === null, 404);

            return $role;
        });

        Route::bind('tenantUser', function (string $value): User {
            $tenantId = auth()->user()?->tenant_id;
            abort_if($tenantId === null, 404);

            $user = User::query()
                ->where('id', $value)
                ->where('tenant_id', $tenantId)
                ->first();
            abort_if($user === null, 404);

            return $user;
        });

        Route::bind('tenantSite', function (string $value): Site {
            $tenantId = auth()->user()?->tenant_id;
            abort_if($tenantId === null, 404);

            $site = Site::query()
                ->where('id', $value)
                ->where('tenant_id', $tenantId)
                ->first();
            abort_if($site === null, 404);

            return $site;
        });

        Route::bind('tenantLocation', function (string $value, \Illuminate\Routing\Route $route): Location {
            $tenantId = auth()->user()?->tenant_id;
            abort_if($tenantId === null, 404);

            $site = $route->parameter('tenantSite');
            abort_if(! $site instanceof Site, 404);

            $location = Location::query()
                ->where('id', $value)
                ->where('tenant_id', $tenantId)
                ->where('site_id', $site->id)
                ->first();
            abort_if($location === null, 404);

            return $location;
        });
    }
}
