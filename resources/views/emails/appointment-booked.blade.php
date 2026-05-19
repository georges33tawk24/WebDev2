<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.mail.appointment_booked_subject') }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #111827;">
    <h1>{{ __('ui.mail.appointment_booked_heading') }}</h1>

    @if($forStaff)
        <p>{{ __('ui.mail.appointment_booked_staff_intro', ['citizen' => $appointment->citizen?->name ?? __('ui.na')]) }}</p>
    @else
        <p>{{ __('ui.mail.appointment_booked_citizen_intro') }}</p>
    @endif

    <p><strong>{{ __('ui.pdf.office') }}:</strong> {{ $appointment->office?->localized('name') ?? __('ui.na') }}</p>
    <p><strong>{{ __('ui.staff.appointment_when') }}:</strong> {{ localized_datetime($appointment->starts_at) }}</p>

    @if($appointment->notes)
        <p><strong>{{ __('ui.citizen.additional_notes') }}:</strong> {{ $appointment->notes }}</p>
    @endif
</body>
</html>
