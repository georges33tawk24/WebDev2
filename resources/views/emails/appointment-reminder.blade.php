<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.mail.appointment_reminder_subject', ['hours' => $hoursBefore]) }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #111827;">
    <h1>{{ __('ui.mail.appointment_reminder_heading', ['hours' => $hoursBefore]) }}</h1>

    @if($forStaff)
        <p>{{ __('ui.mail.appointment_reminder_staff_intro', ['citizen' => $appointment->citizen?->name ?? __('ui.na')]) }}</p>
    @else
        <p>{{ __('ui.mail.appointment_reminder_citizen_intro') }}</p>
    @endif

    <p><strong>{{ __('ui.pdf.office') }}:</strong> {{ $appointment->office?->localized('name') ?? __('ui.na') }}</p>
    <p><strong>{{ __('ui.staff.appointment_when') }}:</strong> {{ localized_datetime($appointment->starts_at) }}</p>

    @if($appointment->notes)
        <p><strong>{{ __('ui.citizen.additional_notes') }}:</strong> {{ $appointment->notes }}</p>
    @endif

    <p style="color: #6b7280; font-size: 14px;">{{ __('ui.mail.appointment_reminder_footer') }}</p>
</body>
</html>
