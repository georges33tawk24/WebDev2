<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.mail.payment_documents_title', ['ref' => $payment->serviceRequest?->reference_number ?? '']) }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827; line-height:1.6;">
    <h2>{{ __('ui.mail.payment_documents_title', ['ref' => $payment->serviceRequest?->reference_number ?? '']) }}</h2>

    <p>{{ __('ui.mail.payment_documents_greeting', ['name' => $payment->serviceRequest?->citizen?->name ?? __('ui.citizen.portal')]) }}</p>

    <p>{{ __('ui.mail.payment_documents_body') }}</p>

    <p>
        <strong>{{ __('ui.citizen.reference_colon') }}</strong>
        {{ $payment->serviceRequest?->reference_number ?? __('ui.na') }}<br>
        <strong>{{ __('ui.table.service') }}:</strong>
        {{ $payment->serviceRequest?->service?->localized('name') ?? __('ui.na') }}<br>
        <strong>{{ __('ui.pdf.amount_paid') }}:</strong>
        {{ localized_number($payment->amount, 2) }} {{ $payment->currency }}<br>
        <strong>{{ __('ui.pdf.paid_at') }}:</strong>
        {{ $payment->paid_at ? localized_datetime($payment->paid_at) : __('ui.na') }}
    </p>

    <p>{{ __('ui.mail.payment_documents_attachments') }}</p>

    <p>{{ __('ui.mail.thank_you') }}<br>{{ config('app.name') }}</p>
</body>
</html>
