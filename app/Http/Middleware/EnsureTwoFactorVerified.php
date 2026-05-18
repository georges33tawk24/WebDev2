<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->two_factor_verified_at !== null) {
            return $next($request);
        }

        return redirect()->route('2fa.verify')
            ->withErrors(['code' => __('ui.flash.2fa_required')]);
    }
}
