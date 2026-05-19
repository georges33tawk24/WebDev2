@extends('layouts.admin')

@section('title', __('ui.citizen.qr_title'))
@section('page-title', __('ui.citizen.qr_title'))

@section('content')
<div class="card" style="max-width:700px; margin:auto; text-align:center;">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:10px;">
        {{ __('ui.citizen.qr_title') }}
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        {{ __('ui.citizen.qr_sub') }}
    </p>

    <div style="margin-bottom:24px; display:flex; justify-content:center;">
        <div style="line-height:0;">
            {!! $qrCode !!}
        </div>
    </div>

    <p><strong>{{ __('ui.citizen.reference_colon') }}</strong> {{ $serviceRequest->reference_number }}</p>
    <p><strong>{{ __('ui.table.service') }}:</strong> {{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</p>
    <p><strong>{{ __('ui.citizen.status_colon') }}</strong> {{ __('ui.status.'.$serviceRequest->status) }}</p>

    @isset($trackingUrl)
        <p style="margin-top:16px; font-size:14px; color:#6b7280; word-break:break-all;">
            {{ __('ui.track.public_notice') }}<br>
            <a href="{{ $trackingUrl }}" style="color:#2563eb;">{{ $trackingUrl }}</a>
        </p>
    @endisset

    <div style="margin-top:28px;">
        <a href="{{ route('citizen.requests') }}"
           class="btn-secondary"
           style="text-decoration:none;">
            {{ __('ui.citizen.back_to_requests') }}
        </a>
    </div>
</div>
@endsection
