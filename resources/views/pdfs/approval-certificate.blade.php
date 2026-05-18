<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Certificate</title>
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

        .certificate-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .certificate-title h2 {
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

        .details {
            margin-bottom: 30px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
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

        .details table tr td:last-child {
            color: #111827;
        }

        .approval-text {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .approval-text p {
            font-size: 15px;
            color: #065f46;
            line-height: 1.6;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature {
            text-align: center;
        }

        .signature .line {
            border-top: 1px solid #111827;
            width: 200px;
            margin-bottom: 6px;
        }

        .signature p {
            font-size: 12px;
            color: #6b7280;
        }

        .stamp {
            width: 100px;
            height: 100px;
            border: 3px solid #1a56db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #1a56db;
            font-size: 11px;
            font-weight: 700;
            padding: 10px;
        }

        .generated-date {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>⚡ E-SERVICES PLATFORM</h1>
    <p>Official Government Services Portal</p>
</div>

<div class="certificate-title">
    <h2>Certificate of Approval</h2>
</div>

<div class="reference">
    Reference Number: <span>{{ $serviceRequest->reference_number }}</span>
</div>

<div class="details">
    <table>
        <tr>
            <td>Citizen Name</td>
            <td>{{ $serviceRequest->citizen?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Service Requested</td>
            <td>{{ $serviceRequest->service?->localized('name') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Government Office</td>
            <td>{{ $serviceRequest->office?->localized('name') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Date Submitted</td>
            <td>{{ $serviceRequest->submitted_at?->format('F d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Date Approved</td>
            <td>{{ now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td><strong style="color:#065f46;">APPROVED</strong></td>
        </tr>
    </table>
</div>

<div class="approval-text">
    <p>
        This is to certify that the above-mentioned service request has been
        <strong>reviewed and approved</strong> by the {{ $serviceRequest->office?->localized('name') ?? 'Government Office' }}.
        The citizen is hereby authorized to proceed with the approved service.
    </p>
</div>

<div class="footer">
    <div class="signature">
        <div class="line"></div>
        <p>Authorized Signature</p>
        <p>{{ $serviceRequest->office?->localized('name') ?? 'Government Office' }}</p>
    </div>
    <div class="stamp">
        OFFICIAL<br>APPROVED<br>✓
    </div>
</div>

<div class="generated-date">
    Generated on {{ now()->format('F d, Y \a\t H:i') }} | E-Services Platform
</div>

</body>
</html>