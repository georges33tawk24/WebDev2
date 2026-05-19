<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function show(string $token): View
    {
        $qr = QrCode::query()
            ->with([
                'serviceRequest.service',
                'serviceRequest.office',
                'serviceRequest.statusHistories' => fn ($query) => $query->orderBy('changed_at'),
            ])
            ->where('token', $token)
            ->firstOrFail();

        if ($qr->expires_at && $qr->expires_at->isPast()) {
            abort(410, __('ui.track.expired'));
        }

        $serviceRequest = $qr->serviceRequest;

        abort_unless($serviceRequest, 404);

        return view('track.show', compact('serviceRequest', 'qr'));
    }
}
