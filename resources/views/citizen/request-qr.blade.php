@extends('layouts.admin')

@section('title', 'Request QR Code')
@section('page-title', 'Request QR Code')

@section('content')
<div class="card" style="max-width:700px; margin:auto; text-align:center;">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:10px;">
        Request QR Code
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        Scan this QR code to track your request.
    </p>

    <div style="margin-bottom:24px;">
        {!! $qrCode !!}
    </div>

    <p><strong>Reference:</strong> {{ $serviceRequest->reference_number }}</p>
    <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($serviceRequest->status) }}</p>

    <div style="margin-top:28px;">
        <a href="{{ route('citizen.requests') }}"
           class="btn-secondary"
           style="text-decoration:none;">
            Back to Requests
        </a>
    </div>
</div>
@endsection