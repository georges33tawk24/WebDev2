<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Response Letter</title>
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

        .letter-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .letter-title h2 {
            font-size: 22px;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .date {
            text-align: right;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .recipient {
            margin-bottom: 20px;
        }

        .recipient p {
            font-size: 14px;
            line-height: 1.8;
            color: #111827;
        }

        .subject {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .subject span {
            font-weight: 700;
            color: #1a56db;
        }

        .body-text {
            font-size: 14px;
            line-height: 1.8;
            color: #374151;
            margin-bottom: 20px;
        }

        .details {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details table tr td {
            padding: 6px 12px;
            font-size: 13px;
        }

        .details table tr td:first-child {
            color: #6b7280;
            width: 35%;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #ede9fe; color: #5b21b6; }

        .signature {
            margin-top: 50px;
        }

        .signature .line {
            border-top: 1px solid #111827;
            width: 200px;
            margin-bottom: 6px;
        }

        .signature p {
            font-size: 13px;
            color: #374151;
            line-height: 1.8;
        }

        .generated-date {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>⚡ E-SERVICES PLATFORM</h1>
    <p>Official Government Services Portal</p>
</div>

<div class="letter-title">
    <h2>Official Response Letter</h2>
</div>

<div class="date">
    {{ now()->format('F d, Y') }}
</div>

<div class="recipient">
    <p><strong>To:</strong> {{ $serviceRequest->citizen?->name ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $serviceRequest->citizen?->email ?? '—' }}</p>
</div>

<div class="subject">
    <p><strong>Subject:</strong> <span>Response to Service Request — {{ $serviceRequest->service?->name ?? '—' }}</span></p>
</div>

<div class="body-text">
    <p>Dear {{ $serviceRequest->citizen?->name ?? 'Citizen' }},</p>
    <br>
    <p>
        We are writing to inform you of the status of your service request submitted to
        {{ $serviceRequest->office?->name ?? 'our office' }}. Please find the details of your request below.
    </p>
</div>

<div class="details">
    <table>
        <tr>
            <td>Reference Number</td>
            <td>{{ $serviceRequest->reference_number }}</td>
        </tr>
        <tr>
            <td>Service Requested</td>
            <td>{{ $serviceRequest->service?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Date Submitted</td>
            <td>{{ $serviceRequest->submitted_at?->format('F d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Current Status</td>
            <td>
                <span class="status-badge status-{{ $serviceRequest->status }}">
                    {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                </span>
            </td>
        </tr>
        @if($serviceRequest->notes)
        <tr>
            <td>Notes</td>
            <td>{{ $serviceRequest->notes }}</td>
        </tr>
        @endif
    </table>
</div>

<div class="body-text">
    <p>
        If you have any questions or require further assistance, please do not hesitate to contact us at
        {{ $serviceRequest->office?->contact_email ?? 'support@eservices.gov' }} or
        {{ $serviceRequest->office?->contact_number ?? 'our office number' }}.
    </p>
    <br>
    <p>Thank you for using the E-Services Platform.</p>
</div>

<div class="signature">
    <div class="line"></div>
    <p><strong>{{ $serviceRequest->office?->name ?? 'Government Office' }}</strong></p>
    <p>E-Services Platform</p>
    <p>{{ $serviceRequest->office?->contact_email ?? '' }}</p>
</div>

<div class="generated-date">
    Generated on {{ now()->format('F d, Y \a\t H:i') }} | E-Services Platform
</div>

</body>
</html>