<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureCitizenIdDocument;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Http\Middleware\NormalizeLocalDevelopmentHost;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        if (env('APP_ENV', 'production') === 'local') {
            $middleware->trustProxies(at: '*');
        }

        $middleware->web(prepend: [
            NormalizeLocalDevelopmentHost::class,
        ]);

        // After StartSession so session('locale') is read/written correctly.
        $middleware->web(append: [
            SetLocale::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->append(SecureHeaders::class);

        $middleware->alias([
            '2fa' => EnsureTwoFactorVerified::class,
            'citizen.id' => EnsureCitizenIdDocument::class,
            'role' => EnsureRole::class,
        ]);

        $middleware->redirectUsersTo(
            fn (Request $request) => route(AuthController::homeRouteFor($request->user()))
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
