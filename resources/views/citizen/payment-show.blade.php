@extends('layouts.admin')

@section('title', __('ui.citizen.payment_details'))
@section('page-title', __('ui.citizen.payment_details'))

@section('content')
<x-form-page>
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:20px;">{{ __('ui.citizen.payment_checkout') }}</h1>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:10px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background:#f9fafb; border-radius:12px; padding:20px; margin-bottom:24px;">
        <p><strong>{{ __('ui.table.service') }}:</strong> {{ $serviceRequest->service->localized('name') ?? __('ui.na') }}</p>
        <p><strong>{{ __('ui.citizen.reference_colon') }}</strong> {{ $serviceRequest->reference_number }}</p>
        <p><strong>{{ __('ui.table.amount') }}:</strong> {{ $dualPrice }}</p>
        @if($lbpRate)
            <p style="font-size:13px; color:#6b7280; margin-top:8px;">{{ __('ui.citizen.exchange_rate_note', ['rate' => localized_digits(number_format($lbpRate, 0, '.', ','))]) }}</p>
        @endif
    </div>

    <div style="margin-bottom:28px;">
        <label style="font-weight:600; display:block; margin-bottom:10px;">{{ __('ui.citizen.payment_method') }}</label>

        @error('payment')
            <div style="background:#fee2e2; color:#991b1b; padding:14px 16px; border-radius:10px; margin-bottom:16px;">
                {{ $message }}
            </div>
        @enderror

        @if ($stripeConfigured)
            <a
                href="{{ route('citizen.payments.checkout', $serviceRequest) }}"
                class="btn-primary"
                style="display:inline-block; margin-bottom:16px; text-decoration:none;"
                id="stripe-checkout-link"
            >{{ __('ui.payments.pay_with_stripe') }}</a>
            <p id="stripe-checkout-loading" style="display:none; font-size:13px; color:#6b7280; margin:0 0 12px;">{{ __('ui.payments.stripe_redirecting') }}</p>
            <p style="font-size:13px; color:#6b7280; margin:0 0 20px;">
                {{ __('ui.payments.stripe_checkout_hint') }}
            </p>
            <p style="font-size:12px; color:#9ca3af; margin:0 0 20px; background:#eff6ff; padding:12px; border-radius:8px;">
                {{ __('ui.payments.stripe_test_cards_hint') }}
            </p>
        @else
            <p style="font-size:13px; color:#b45309; background:#fffbeb; padding:12px; border-radius:8px; margin-bottom:20px;">
                {{ __('ui.payments.stripe_setup_required') }}
            </p>
        @endif

        @if ($cryptoConfigured)
            @if($cryptoAmountTooLow && $cryptoMinUsd)
                <p style="font-size:13px; color:#92400e; background:#fffbeb; padding:12px; border-radius:8px; margin-bottom:12px;">
                    {{ __('ui.payments.crypto_minimum_notice', ['min' => localized_money($cryptoMinUsd)]) }}
                </p>
                <p style="font-size:13px; color:#6b7280; margin:0 0 20px;">
                    {{ __('ui.payments.crypto_use_stripe_for_small') }}
                </p>
            @else
                <a
                    href="{{ route('citizen.payments.crypto.checkout', $serviceRequest) }}"
                    class="btn-primary"
                    style="display:inline-block; margin-bottom:12px; text-decoration:none; background:#0f766e;"
                >{{ __('ui.payments.pay_with_crypto') }}</a>
                <p style="font-size:13px; color:#6b7280; margin:0 0 12px;">
                    {{ __('ui.payments.crypto_checkout_hint') }}
                </p>
                @if($cryptoMinUsd)
                    <p style="font-size:12px; color:#6b7280; margin:0 0 12px;">
                        {{ __('ui.payments.crypto_minimum_notice', ['min' => localized_money($cryptoMinUsd)]) }}
                    </p>
                @endif
                @if($cryptoSandbox)
                    <p style="font-size:12px; color:#1e40af; margin:0 0 20px; background:#eff6ff; padding:12px; border-radius:8px;">
                        {{ __('ui.payments.crypto_sandbox_hint') }}
                    </p>
                @endif
            @endif
        @else
            <p style="font-size:13px; color:#b45309; background:#fffbeb; padding:12px; border-radius:8px; margin-bottom:20px;">
                {{ __('ui.payments.crypto_setup_required') }}
            </p>
        @endif
    </div>
</div>
</x-form-page>
@push('scripts')
<script>
    document.getElementById('stripe-checkout-link')?.addEventListener('click', function () {
        var el = document.getElementById('stripe-checkout-loading');
        if (el) {
            el.style.display = 'block';
        }
        this.style.opacity = '0.7';
        this.style.pointerEvents = 'none';
    });
</script>
@endpush
@endsection
