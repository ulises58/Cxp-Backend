<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Spatie Permission "teams": fija el tenant actual para resolver roles/permisos.
 * Landlord (User.tenant_id null) usa permission.platform_team_id.
 */
class SetPermissionsTeamFromAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if ($user->tenant_id === null) {
                setPermissionsTeamId(config('permission.platform_team_id'));
            } else {
                setPermissionsTeamId($user->tenant_id);
            }
        } else {
            setPermissionsTeamId(null);
        }

        return $next($request);
    }
}
