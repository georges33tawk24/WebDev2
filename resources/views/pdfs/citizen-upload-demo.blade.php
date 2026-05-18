<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo Citizen Upload</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; padding: 40px; }
        h1 { font-size: 20px; color: #1e429f; margin-bottom: 16px; }
        p { font-size: 13px; line-height: 1.6; color: #374151; }
        .meta { margin-top: 24px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Demo supporting document</h1>
    <p>This PDF was generated for local development and QA. It stands in for a citizen-uploaded file during demo seeding.</p>
    @if (! empty($reference))
        <p class="meta">Request reference: {{ $reference }}</p>
    @endif
</body>
</html>
