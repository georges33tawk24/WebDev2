<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('ui.mail.request_status_updated_title') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827;">
    <h2>{{ __('ui.mail.request_status_updated_title') }}</h2>

    <p>{{ __('ui.mail.request_submitted_greeting', ['name' => $serviceRequest->citizen->name ?? __('ui.citizen.portal')]) }}</p>

    <p>{{ __('ui.mail.request_status_updated_body') }}</p>

    <p><strong>{{ __('ui.citizen.reference_colon') }}</strong> {{ $serviceRequest->reference_number }}</p>
    <p><strong>{{ __('ui.table.service') }}:</strong> {{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</p>
    <p><strong>{{ __('ui.citizen.office_colon') }}</strong> {{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</p>
    <p><strong>{{ __('ui.mail.previous_status') }}</strong> {{ __('ui.status.'.$previousStatus) }}</p>
    <p><strong>{{ __('ui.citizen.status_colon') }}</strong> {{ __('ui.status.'.$serviceRequest->status) }}</p>

    @if($comment)
        <p><strong>{{ __('ui.staff.comment_optional') }}</strong> {{ $comment }}</p>
    @endif

    <p>{{ __('ui.mail.track_from_dashboard') }}</p>

    <p>{{ __('ui.mail.thank_you') }}<br>{{ config('app.name') }}</p>
</body>
</html>
