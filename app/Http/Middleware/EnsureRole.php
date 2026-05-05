<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role?->slug !== $role) {
            $targetRoute = match ($user?->role?->slug) {
                'admin' => 'dashboard.admin',
                'office_staff' => 'dashboard.staff',
                default => 'dashboard.citizen',
            };

            return redirect()->route($targetRoute)
                ->withErrors(['role' => 'You are not authorized to access that page.']);
        }

        return $next($request);
    }
}
