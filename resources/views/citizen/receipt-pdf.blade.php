<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.pdf.receipt_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; padding: 32px; }
        h1 { font-size: 22px; color: #1e429f; margin-bottom: 8px; }
        p { font-size: 13px; line-height: 1.7; margin: 6px 0; }
        .label { color: #6b7280; font-weight: 600; }
        .amount { font-size: 18px; font-weight: 700; color: #1a56db; margin-top: 16px; }
    </style>
</head>
<body>
    <h1>{{ __('ui.pdf.receipt_title') }}</h1>
    <p style="color:#6b7280; margin-bottom:20px;">{{ __('ui.pdf.municipal_platform') }}</p>

    <p><span class="label">{{ __('ui.pdf.reference') }}:</span> {{ $serviceRequest->reference_number }}</p>
    <p><span class="label">{{ __('ui.pdf.service') }}:</span> {{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</p>
    <p><span class="label">{{ __('ui.pdf.office') }}:</span> {{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</p>
    <p class="amount">{{ __('ui.pdf.amount_paid') }}: {{ localized_money($payment->amount) }}</p>
    <p><span class="label">{{ __('ui.pdf.payment_method') }}:</span> {{ ucfirst($payment->method) }}</p>
    <p><span class="label">{{ __('ui.pdf.status') }}:</span> {{ __('ui.status.'.$payment->status) }}</p>
    <p><span class="label">{{ __('ui.pdf.paid_at') }}:</span> {{ $payment->paid_at ? localized_datetime($payment->paid_at) : __('ui.na') }}</p>
    @if($payment->gateway_reference)
        <p><span class="label">{{ __('ui.pdf.gateway_reference') }}:</span> {{ $payment->gateway_reference }}</p>
    @endif
</body>
</html>
