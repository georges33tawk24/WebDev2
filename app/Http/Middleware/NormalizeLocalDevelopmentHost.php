<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeLocalDevelopmentHost
{
    /**
     * Keep local traffic on the host from APP_URL (localhost vs 127.0.0.1) so OAuth cookies match.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $canonical = parse_url((string) config('app.url'));
        $canonicalHost = $canonical['host'] ?? null;
        $canonicalScheme = $canonical['scheme'] ?? 'http';
        $canonicalPort = $canonical['port'] ?? ($canonicalScheme === 'https' ? 443 : 80);

        if (! is_string($canonicalHost) || $canonicalHost === '') {
            return $next($request);
        }

        $localHosts = ['localhost', '127.0.0.1'];
        $requestHost = $request->getHost();

        if (! in_array($requestHost, $localHosts, true) || $requestHost === $canonicalHost) {
            return $next($request);
        }

        $portSuffix = in_array((int) $canonicalPort, [80, 443], true) ? '' : ":{$canonicalPort}";

        return redirect()->to($canonicalScheme.'://'.$canonicalHost.$portSuffix.$request->getRequestUri());
    }
}
