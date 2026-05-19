@extends('layouts.admin')

@section('title', __('ui.citizen.crypto_payment_title'))
@section('page-title', __('ui.citizen.crypto_payment_title'))

@section('content')
<x-form-page>
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:12px;">{{ __('ui.citizen.crypto_payment_title') }}</h1>

    @if($isSandbox)
        <p style="background:#eff6ff; color:#1e40af; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
            {{ __('ui.payments.crypto_sandbox_hint') }}
        </p>
    @endif

    <div
        id="crypto-payment-status"
        role="status"
        aria-live="polite"
        hidden
    ></div>

    <div style="background:#f9fafb; border-radius:12px; padding:20px; margin-bottom:24px;">
        <p><strong>{{ __('ui.table.service') }}:</strong> {{ $serviceRequest->service->localized('name') ?? __('ui.na') }}</p>
        <p><strong>{{ __('ui.citizen.reference_colon') }}</strong> {{ $serviceRequest->reference_number }}</p>
        <p><strong>{{ __('ui.table.amount') }}:</strong> {{ localized_money($payment->amount) }}</p>
    </div>

    @if($invoiceUrl)
        <p style="font-size:15px; color:#374151; margin-bottom:20px;">{{ __('ui.citizen.crypto_browser_hint') }}</p>

        <div class="form-actions" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
            <a href="{{ $invoiceUrl }}" class="btn-primary" style="text-decoration:none;" target="_blank" rel="noopener noreferrer">
                {{ __('ui.citizen.crypto_open_checkout') }}
            </a>
            <a href="{{ route('citizen.payments.crypto.cancel', $serviceRequest) }}" class="btn-secondary" style="text-decoration:none;">
                {{ __('ui.cancel') }}
            </a>
        </div>
        <p style="font-size:13px; color:#6b7280; margin-bottom:24px;">
            <a href="#" id="crypto-check-now" style="color:#1a56db;">{{ __('ui.citizen.crypto_check_now') }}</a>
        </p>
    @endif

    @if($payAddress && $payAmount)
        <details style="margin-bottom:24px;">
            <summary style="cursor:pointer; font-weight:600; color:#4b5563; margin-bottom:12px;">{{ __('ui.citizen.crypto_manual_option') }}</summary>
            <div style="text-align:center; margin-top:16px;">
                <p style="font-weight:600; margin-bottom:8px;">{{ __('ui.citizen.crypto_send_exact') }}</p>
                <p style="font-size:22px; font-weight:700; color:#1a56db; margin-bottom:16px;">
                    {{ localized_number($payAmount, 8) }} <span style="font-size:16px;">{{ strtoupper($payCurrency) }}</span>
                </p>
                <div style="display:inline-block; padding:12px; background:#fff; border:1px solid #e5e7eb; border-radius:12px;">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($payAddress) !!}
                </div>
                <p style="font-size:13px; color:#6b7280; margin-top:16px; word-break:break-all; max-width:100%;">
                    {{ __('ui.citizen.crypto_wallet') }}:<br>
                    <strong>{{ $payAddress }}</strong>
                </p>
            </div>
        </details>
    @endif

    @if(!$invoiceUrl && !($payAddress && $payAmount))
        <p style="color:#991b1b;">{{ __('ui.flash.crypto_checkout_failed') }}</p>
        <a href="{{ route('citizen.payments.show', $serviceRequest) }}" class="btn-secondary" style="text-decoration:none; margin-top:16px; display:inline-block;">{{ __('ui.back') }}</a>
    @endif
</div>

<div
    id="crypto-payment-poll-root"
    hidden
    data-config='@json($pollConfig)'
></div>
</x-form-page>
@endsection

@push('scripts')
    @vite('resources/js/crypto-payment-poll.js')
@endpush
