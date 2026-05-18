<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.pdf.receipt_title') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #111827; padding: 40px; }

        .header {
            text-align: center;
            border-bottom: 3px solid #1a56db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #1e429f;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .header p {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }

        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-title h2 {
            font-size: 22px;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .reference {
            text-align: center;
            background: #f3f4f6;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #374151;
        }

        .reference span {
            font-weight: 700;
            color: #1a56db;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details table tr td {
            padding: 10px 12px;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .details table tr td:first-child {
            color: #6b7280;
            width: 35%;
            font-weight: 600;
        }

        .amount-box {
            background: #1e429f;
            color: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .amount-box p {
            font-size: 13px;
            opacity: 0.8;
            margin-bottom: 6px;
        }

        .amount-box h3 {
            font-size: 36px;
            font-weight: 700;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.8;
        }

        .generated-date {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>{{ __('ui.pdf.municipal_platform') }}</p>
</div>

<div class="receipt-title">
    <h2>{{ __('ui.pdf.receipt_title') }}</h2>
</div>

<div class="reference">
    Receipt Number: <span>REC-{{ strtoupper(substr($serviceRequest->reference_number, 0, 8)) }}</span>
</div>

<div class="details">
    <table>
        <tr>
            <td>Citizen Name</td>
            <td>{{ $serviceRequest->citizen?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>{{ $serviceRequest->citizen?->email ?? '—' }}</td>
        </tr>
        <tr>
            <td>Service</td>
            <td>{{ $serviceRequest->service?->localized('name') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Government Office</td>
            <td>{{ $serviceRequest->office?->localized('name') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Date</td>
            <td>{{ now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td>Payment Status</td>
            <td><strong style="color:#065f46;">PAID</strong></td>
        </tr>
    </table>
</div>

<div class="amount-box">
    <p>Total Amount Paid</p>
    <h3>${{ number_format($serviceRequest->service?->price ?? 0, 2) }}</h3>
</div>

<div class="footer">
    <p>Thank you for using the E-Services Platform.</p>
    <p>This is an official receipt. Please keep it for your records.</p>
    <p>For any inquiries, contact {{ $serviceRequest->office?->contact_email ?? 'support@eservices.gov' }}</p>
</div>

<div class="generated-date">
    Generated on {{ now()->format('F d, Y \a\t H:i') }} | E-Services Platform
</div>

</body>
</html>