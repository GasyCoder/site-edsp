<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    /** @var list<string> */
    private const SUPPORTED_LOCALES = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin*')) {
            $locale = (string) $request->cookie('edsp_locale', config('app.locale', 'fr'));
            app()->setLocale(in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : 'fr');
        }

        return $next($request);
    }
}
