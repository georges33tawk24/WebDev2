<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\View\View;

class PublicTrackingController extends Controller
{
    public function show(string $token): View
    {
        $qrCode = QrCode::with([
            'serviceRequest.service',
            'serviceRequest.office',
            'serviceRequest.statusHistories',
        ])
        ->where('token', $token)
        ->firstOrFail();

        $serviceRequest = $qrCode->serviceRequest;

        return view('public.track-request', compact(
            'serviceRequest',
            'qrCode'
        ));
    }
}