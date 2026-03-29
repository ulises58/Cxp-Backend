<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlordStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || $user->tenant_id !== null) {
            abort(Response::HTTP_FORBIDDEN, __('api.landlord_only'));
        }

        if (! $user->can('landlord.panel')) {
            abort(Response::HTTP_FORBIDDEN, __('api.landlord_access_denied'));
        }

        return $next($request);
    }
}
