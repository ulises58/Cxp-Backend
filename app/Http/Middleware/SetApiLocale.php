<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getPreferredLanguage(config('app.supported_locales', ['es', 'en']));
        if ($locale !== null) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
