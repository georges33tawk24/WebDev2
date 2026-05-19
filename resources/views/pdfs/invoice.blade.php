<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.pdf.invoice_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; padding: 32px; }
        h1 { font-size: 22px; color: #1e429f; margin-bottom: 4px; }
        .subtitle { color: #6b7280; font-size: 13px; margin-bottom: 24px; }
        .meta { background: #f3f4f6; padding: 14px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; }
        .meta p { margin: 4px 0; }
        .label { color: #6b7280; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px 12px; text-align: start; }
        th { background: #f9fafb; color: #374151; }
        .total { font-size: 18px; font-weight: 700; color: #1a56db; text-align: end; margin-top: 16px; }
        .paid-stamp { color: #065f46; font-weight: 700; margin-top: 12px; font-size: 14px; }
        .footer { margin-top: 32px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ __('ui.pdf.invoice_title') }}</h1>
    <p class="subtitle">{{ __('ui.pdf.municipal_platform') }}</p>

    <div class="meta">
        <p><span class="label">{{ __('ui.pdf.invoice_number') }}:</span> INV-{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p><span class="label">{{ __('ui.pdf.reference') }}:</span> {{ $serviceRequest->reference_number }}</p>
        <p><span class="label">{{ __('ui.pdf.issued_at') }}:</span> {{ localized_datetime($payment->paid_at ?? now()) }}</p>
    </div>

    <p><span class="label">{{ __('ui.pdf.bill_to') }}:</span> {{ $serviceRequest->citizen?->name ?? __('ui.na') }}</p>
    <p style="font-size:13px; color:#6b7280;">{{ $serviceRequest->citizen?->email ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('ui.pdf.description') }}</th>
                <th>{{ __('ui.table.office') }}</th>
                <th>{{ __('ui.table.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</td>
                <td>{{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</td>
                <td>{{ localized_number($payment->amount, 2) }} {{ $payment->currency }}</td>
            </tr>
        </tbody>
    </table>

    <p class="total">{{ __('ui.pdf.total') }}: {{ localized_number($payment->amount, 2) }} {{ $payment->currency }}</p>
    <p class="paid-stamp">{{ __('ui.pdf.invoice_paid') }}</p>
    <p style="font-size:13px; margin-top:8px;">
        <span class="label">{{ __('ui.pdf.payment_method') }}:</span>
        {{ __('ui.payments.method_'.$payment->method) }}
    </p>
    @if($payment->gateway_reference)
        <p style="font-size:13px;">
            <span class="label">{{ __('ui.pdf.gateway_reference') }}:</span>
            {{ $payment->gateway_reference }}
        </p>
    @endif

    <p class="footer">{{ __('ui.pdf.invoice_footer') }}</p>

    @include('pdfs.partials.status-history', ['serviceRequest' => $serviceRequest])
</body>
</html>
