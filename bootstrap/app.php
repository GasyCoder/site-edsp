<?php

use App\Http\Middleware\EnsureSiteIsAvailable;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetPublicLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SetPublicLocale::class, EnsureSiteIsAvailable::class, HandleInertiaRequests::class]);
        $middleware->redirectGuestsTo(fn (Request $request): string => route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
