<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyFromAuthenticatedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || $user->tenant_id === null) {
            abort(Response::HTTP_FORBIDDEN, __('api.tenant_context_required'));
        }

        $tenant = Tenant::query()->find($user->tenant_id);
        if ($tenant === null) {
            abort(Response::HTTP_FORBIDDEN, __('api.tenant_not_found'));
        }

        if (! $tenant->is_active) {
            abort(Response::HTTP_FORBIDDEN, __('api.tenant_inactive'));
        }

        Tenancy::initialize($tenant);

        return $next($request);
    }
}
