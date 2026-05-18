<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Request</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f3f4f6;
            margin:0;
            padding:40px;
        }

        .container{
            max-width:900px;
            margin:auto;
        }

        .card{
            background:white;
            border-radius:16px;
            padding:28px;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
            margin-bottom:24px;
        }

        h1{
            margin-top:0;
            color:#111827;
        }

        .label{
            font-weight:bold;
            color:#374151;
        }

        .status{
            display:inline-block;
            padding:8px 14px;
            border-radius:999px;
            background:#dbeafe;
            color:#1d4ed8;
            font-weight:600;
            margin-top:8px;
        }

        .timeline{
            border-left:3px solid #2563eb;
            padding-left:18px;
            margin-top:20px;
        }

        .timeline-item{
            margin-bottom:20px;
        }

        .timeline-item h4{
            margin:0 0 4px;
        }

        .timeline-item small{
            color:#6b7280;
        }

        .security-note{
            font-size:14px;
            color:#6b7280;
            line-height:1.7;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">
        <h1>Request Tracking</h1>

        <p>
            <span class="label">Reference Number:</span>
            {{ $serviceRequest->reference_number }}
        </p>

        <p>
            <span class="label">Service:</span>
            {{ $serviceRequest->service->name ?? 'N/A' }}
        </p>

        <p>
            <span class="label">Office:</span>
            {{ $serviceRequest->office->name ?? 'N/A' }}
        </p>

        <p>
            <span class="label">Current Status:</span>
        </p>

        <div class="status">
            {{ ucwords(str_replace('_', ' ', $serviceRequest->status)) }}
        </div>

        <div style="margin-top:24px;">
            <span class="label">Submitted At:</span>
            {{ optional($serviceRequest->submitted_at)->format('d M Y - h:i A') }}
        </div>
    </div>

    <div class="card">
        <h2>Status Timeline</h2>

        <div class="timeline">

            @forelse($serviceRequest->statusHistories->sortByDesc('changed_at') as $history)

                <div class="timeline-item">
                    <h4>
                        {{ ucwords(str_replace('_', ' ', $history->to_status)) }}
                    </h4>

                    <small>
                        {{ optional($history->changed_at)->format('d M Y - h:i A') }}
                    </small>

                    @if($history->comment)
                        <p>
                            {{ $history->comment }}
                        </p>
                    @endif
                </div>

            @empty

                <p>No status history available.</p>

            @endforelse

        </div>
    </div>

    <div class="card">
        <h3>Privacy & Security</h3>

        <p class="security-note">
            This tracking page only shows limited request information.
            Sensitive citizen data, uploaded documents, and payment details
            are intentionally hidden for privacy and security reasons.
        </p>
    </div>

</div>

</body>
</html>