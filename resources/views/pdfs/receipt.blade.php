<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.pdf.receipt_title') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #111827; padding: 40px; }
        .header { text-align: center; border-bottom: 3px solid #1a56db; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; color: #1e429f; font-weight: 700; }
        .header p { font-size: 14px; color: #6b7280; margin-top: 4px; }
        .receipt-title { text-align: center; margin-bottom: 30px; }
        .receipt-title h2 { font-size: 22px; color: #1a56db; text-transform: uppercase; letter-spacing: 2px; }
        .reference { text-align: center; background: #f3f4f6; padding: 12px; border-radius: 8px; margin-bottom: 30px; font-size: 14px; }
        .details table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details table tr td { padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e5e7eb; }
        .details table tr td:first-child { color: #6b7280; width: 35%; font-weight: 600; }
        .amount-box { background: #1e429f; color: #fff; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 30px; }
        .amount-box p { font-size: 13px; opacity: 0.8; margin-bottom: 6px; }
        .amount-box h3 { font-size: 36px; font-weight: 700; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; line-height: 1.8; }
        .generated-date { text-align: center; margin-top: 20px; font-size: 11px; color: #9ca3af; }
        .unpaid-note { color: #991b1b; font-weight: 600; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

@php
    $paidPayment = $payment ?? $serviceRequest->latestPaidPayment();
    $amount = $paidPayment ? (float) $paidPayment->amount : (float) ($serviceRequest->service?->price ?? 0);
@endphp

<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>{{ __('ui.pdf.municipal_platform') }}</p>
</div>

<div class="receipt-title">
    <h2>{{ __('ui.pdf.receipt_title') }}</h2>
</div>

<div class="reference">
    {{ __('ui.pdf.reference') }}: <strong>{{ $serviceRequest->reference_number }}</strong>
</div>

<div class="details">
    <table>
        <tr>
            <td>{{ __('ui.pdf.citizen') }}</td>
            <td>{{ $serviceRequest->citizen?->name ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.table.email') }}</td>
            <td>{{ $serviceRequest->citizen?->email ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.pdf.service') }}</td>
            <td>{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.pdf.office') }}</td>
            <td>{{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td>{{ __('ui.pdf.status') }}</td>
            <td>
                @if($paidPayment)
                    <strong style="color:#065f46;">{{ __('ui.status.paid') }}</strong>
                @else
                    <strong style="color:#991b1b;">{{ __('ui.citizen.unpaid') }}</strong>
                @endif
            </td>
        </tr>
        @if($paidPayment)
            <tr>
                <td>{{ __('ui.pdf.payment_method') }}</td>
                <td>{{ __('ui.payments.method_'.$paidPayment->method) }}</td>
            </tr>
            <tr>
                <td>{{ __('ui.pdf.paid_at') }}</td>
                <td>{{ $paidPayment->paid_at ? localized_datetime($paidPayment->paid_at) : __('ui.na') }}</td>
            </tr>
        @endif
    </table>
</div>

@if($paidPayment)
    <div class="amount-box">
        <p>{{ __('ui.pdf.amount_paid') }}</p>
        <h3>{{ localized_number($amount, 2) }} {{ $paidPayment->currency }}</h3>
    </div>
@else
    <p class="unpaid-note">{{ __('ui.citizen.unpaid') }}</p>
@endif

@include('pdfs.partials.status-history', ['serviceRequest' => $serviceRequest])

<div class="footer">
    <p>{{ __('ui.pdf.receipt_footer_thanks') }}</p>
    <p>{{ __('ui.pdf.receipt_footer_keep') }}</p>
</div>

<div class="generated-date">
    {{ __('ui.pdf.generated_on', ['when' => localized_datetime(now())]) }}
</div>

</body>
</html>
