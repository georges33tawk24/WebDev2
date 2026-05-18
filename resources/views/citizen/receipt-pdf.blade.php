<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h1>Payment Receipt</h1>

    <p><strong>Reference:</strong> {{ $serviceRequest->reference_number }}</p>
    <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
    <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'N/A' }}</p>
    <p><strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
    <p><strong>Payment Method:</strong> {{ ucfirst($payment->method) }}</p>
    <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
    <p><strong>Paid At:</strong> {{ optional($payment->paid_at)->format('d M Y - h:i A') }}</p>
    <p><strong>Gateway Reference:</strong> {{ $payment->gateway_reference }}</p>
</body>
</html>