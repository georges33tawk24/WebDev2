<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role?->slug !== $role) {
            $targetRoute = AuthController::homeRouteFor($user);

            if ($request->routeIs($targetRoute)) {
                abort(403, 'Your account does not have permission to access this area.');
            }

            return redirect()->route($targetRoute)
                ->withErrors(['role' => __('ui.flash.unauthorized_role')]);
        }

        return $next($request);
    }
}
