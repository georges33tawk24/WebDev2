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

        if ($user?->isCitizen()) {
            $user->purgeInvalidIdDocumentPath();
            $user->refresh();
        }

        if ($user?->needsIdDocument() && ! $request->routeIs(
            'id-upload',
            'id-upload.store',
            'api.id-document.parse',
            'logout',
            '2fa.verify',
            '2fa.verify.submit',
            '2fa.resend',
            '2fa.defer',
            '2fa.collect-email',
            '2fa.collect-email.store',
        )) {
            return redirect()
                ->route('id-upload')
                ->with('status', 'Please upload your ID document to continue.');
        }

        return $next($request);
    }
}
