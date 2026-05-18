<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Service Request Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827;">
    <h2>Service Request Submitted Successfully</h2>

    <p>Hello {{ $serviceRequest->citizen->name ?? 'Citizen' }},</p>

    <p>Your service request has been submitted successfully.</p>

    <p><strong>Reference Number:</strong> {{ $serviceRequest->reference_number }}</p>
    <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
    <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($serviceRequest->status) }}</p>
    <p><strong>Submitted At:</strong> {{ optional($serviceRequest->created_at)->format('d M Y - h:i A') }}</p>

    <p>You can track your request from your citizen dashboard.</p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>