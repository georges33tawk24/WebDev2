<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $isLocal = app()->environment('local');

        $scriptSrc = $isLocal
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:5173 http://localhost:5173 http://[::1]:5173 https://maps.googleapis.com https://maps.gstatic.com"
            : "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://maps.gstatic.com";

        $styleSrc = $isLocal
            ? "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://127.0.0.1:5173 http://localhost:5173 http://[::1]:5173"
            : "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com";

        $connectSrc = $isLocal
            ? "connect-src 'self' http://127.0.0.1:5173 http://localhost:5173 http://[::1]:5173 ws://127.0.0.1:5173 ws://localhost:5173 ws://[::1]:5173 https://maps.googleapis.com https://maps.gstatic.com"
            : "connect-src 'self' https://maps.googleapis.com https://maps.gstatic.com";

        $imgSrc = "img-src 'self' data: blob: https://maps.googleapis.com https://maps.gstatic.com";

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        $stripeNav = 'https://checkout.stripe.com https://*.stripe.com';

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; {$scriptSrc}; {$styleSrc}; font-src 'self' https://fonts.gstatic.com data:; {$imgSrc}; {$connectSrc}; frame-ancestors 'none'; base-uri 'self'; form-action 'self' {$stripeNav}; navigate-to 'self' {$stripeNav}"
        );

        return $response;
    }
}