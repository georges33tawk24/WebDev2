<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCitizenIdDocument
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->needsIdDocument() && ! $request->routeIs('id-upload', 'id-upload.store', 'logout')) {
            return redirect()
                ->route('id-upload')
                ->with('status', 'Please upload your ID document to continue.');
        }

        return $next($request);
    }
}
