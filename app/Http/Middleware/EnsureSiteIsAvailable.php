<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*', 'up') || $request->user()?->can('access admin')) {
            return $next($request);
        }

        $maintenanceMode = Cache::rememberForever(
            'settings.maintenance_mode',
            fn (): bool => filter_var(
                Setting::query()->where('key', 'maintenance_mode')->value('value') ?? false,
                FILTER_VALIDATE_BOOL,
            ),
        );

        if ($maintenanceMode) {
            return response()->view('errors.503', status: 503);
        }

        return $next($request);
    }
}
