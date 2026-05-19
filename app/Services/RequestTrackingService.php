<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class RequestTrackingService
{
    public function ensureQrToken(ServiceRequest $serviceRequest): QrCode
    {
        return QrCode::query()->firstOrCreate(
            ['service_request_id' => $serviceRequest->id],
            [
                'token' => strtoupper(Str::random(12)),
                'expires_at' => now()->addYear(),
            ]
        );
    }

    public function trackingUrl(ServiceRequest $serviceRequest): string
    {
        $qr = $this->ensureQrToken($serviceRequest);

        return route('track.show', ['token' => $qr->token]);
    }
}
