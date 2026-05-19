@props(['serviceRequest'])

@php
    $paidPayment = $serviceRequest->latestPaidPayment();
    $isPaid = $serviceRequest->isPaid();
@endphp

<div {{ $attributes->merge(['class' => 'request-payment-status']) }}>
    @if ($isPaid && $paidPayment)
        <span style="display:inline-block; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600; background:#dcfce7; color:#166534;">
            {{ __('ui.citizen.paid') }}
        </span>
        <div style="font-size:13px; color:#374151; margin-top:8px; line-height:1.6;">
            <div>
                <span style="color:#6b7280;">{{ __('ui.pdf.amount_paid') }}:</span>
                <strong>{{ localized_number($paidPayment->amount, 2) }} {{ $paidPayment->currency }}</strong>
            </div>
            <div>
                <span style="color:#6b7280;">{{ __('ui.pdf.payment_method') }}:</span>
                {{ __('ui.payments.method_'.$paidPayment->method) }}
            </div>
            @if ($paidPayment->paid_at)
                <div>
                    <span style="color:#6b7280;">{{ __('ui.pdf.paid_at') }}:</span>
                    {{ localized_datetime($paidPayment->paid_at) }}
                </div>
            @endif
        </div>
    @else
        <span style="display:inline-block; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600; background:#fee2e2; color:#991b1b;">
            {{ __('ui.citizen.unpaid') }}
        </span>
    @endif
</div>
