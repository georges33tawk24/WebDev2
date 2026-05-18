@extends('layouts.admin')

@section('title', 'Request QR Code')
@section('page-title', 'Request QR Code')

@section('content')

<div class="card" style="max-width:850px; margin:auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">
                Request Tracking QR
            </h1>

            <p style="color:#6b7280; line-height:1.7;">
                This QR code allows quick access to your public request tracking page.
                Citizens can scan it using any mobile camera application to instantly
                check the latest request status without logging into the platform.
            </p>
        </div>
    </div>

    <div style="
        display:grid;
        grid-template-columns: 320px 1fr;
        gap:32px;
        align-items:start;
    ">

        <div style="
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:18px;
            padding:28px;
            text-align:center;
        ">

            <img
                src="{{ $qrCodePath }}"
                alt="QR Code"
                style="
                    width:100%;
                    max-width:260px;
                    border-radius:12px;
                    background:white;
                    padding:14px;
                    border:1px solid #d1d5db;
                "
            >

            <div style="margin-top:22px;">
                <a
                    href="{{ $qrCodePath }}"
                    download
                    class="btn-primary"
                    style="text-decoration:none;"
                >
                    Download QR Code
                </a>
            </div>
        </div>

        <div>

            <div style="
                background:#ffffff;
                border:1px solid #e5e7eb;
                border-radius:18px;
                padding:24px;
                margin-bottom:22px;
            ">

                <h2 style="
                    font-size:20px;
                    font-weight:700;
                    margin-bottom:20px;
                ">
                    Request Information
                </h2>

                <div style="display:grid; gap:14px;">

                    <div>
                        <div style="font-size:13px; color:#6b7280;">
                            Reference Number
                        </div>

                        <div style="font-weight:600;">
                            {{ $serviceRequest->reference_number }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size:13px; color:#6b7280;">
                            Government Service
                        </div>

                        <div style="font-weight:600;">
                            {{ $serviceRequest->service->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size:13px; color:#6b7280;">
                            Current Status
                        </div>

                        <div style="
                            display:inline-block;
                            margin-top:6px;
                            padding:8px 14px;
                            border-radius:999px;
                            background:#dbeafe;
                            color:#1d4ed8;
                            font-weight:600;
                            font-size:14px;
                        ">
                            {{ ucwords(str_replace('_', ' ', $serviceRequest->status)) }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size:13px; color:#6b7280;">
                            Submitted At
                        </div>

                        <div style="font-weight:600;">
                            {{ optional($serviceRequest->submitted_at)->format('d M Y - h:i A') }}
                        </div>
                    </div>

                </div>

            </div>

            <div style="
                background:#eff6ff;
                border:1px solid #bfdbfe;
                border-radius:18px;
                padding:22px;
            ">

                <h3 style="
                    font-size:18px;
                    font-weight:700;
                    margin-bottom:14px;
                    color:#1e3a8a;
                ">
                    Public Tracking Link
                </h3>

                <p style="
                    color:#1e40af;
                    line-height:1.7;
                    margin-bottom:16px;
                    word-break:break-all;
                ">
                    {{ $trackingUrl }}
                </p>

                <a
                    href="{{ $trackingUrl }}"
                    target="_blank"
                    class="btn-secondary"
                    style="text-decoration:none;"
                >
                    Open Public Tracking Page
                </a>

            </div>

        </div>

    </div>

</div>

@endsection