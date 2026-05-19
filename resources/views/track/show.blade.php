<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.track.title') }} — {{ $serviceRequest->reference_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; margin: 0; padding: 24px; }
        .track-card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .track-ref { font-size: 13px; color: #6b7280; }
        h1 { margin: 8px 0 20px; font-size: 24px; color: #111827; }
        .meta { color: #4b5563; line-height: 1.7; margin-bottom: 24px; }
        .timeline { border-inline-start: 3px solid #e5e7eb; padding-inline-start: 20px; margin-inline-start: 8px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-in-review { background: #dbeafe; color: #1e40af; }
        .badge-missing-documents { background: #ffedd5; color: #9a3412; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-completed { background: #ede9fe; color: #5b21b6; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .timeline-item { margin-bottom: 16px; }
        .timeline-item strong { display: block; color: #111827; }
        .timeline-item span { font-size: 13px; color: #6b7280; }
        .login-hint { margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 14px; }
        .login-hint a { color: #2563eb; font-weight: 600; }
    </style>
</head>
<body>
    <div class="track-card">
        <p class="track-ref">{{ __('ui.track.public_notice') }}</p>
        <h1>{{ __('ui.track.request_status') }}</h1>

        <div class="meta">
            <p><strong>{{ __('ui.table.reference') }}:</strong> {{ $serviceRequest->reference_number }}</p>
            <p><strong>{{ __('ui.table.service') }}:</strong> {{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</p>
            <p><strong>{{ __('ui.table.office') }}:</strong> {{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</p>
            <p><strong>{{ __('ui.staff.current_status') }}:</strong>
                <span class="badge badge-{{ str_replace('_', '-', $serviceRequest->status) }}">{{ __('ui.status.'.$serviceRequest->status) }}</span>
            </p>
        </div>

        <h2 style="font-size:16px; margin-bottom:12px;">{{ __('ui.track.timeline') }}</h2>
        <div class="timeline">
            @forelse($serviceRequest->statusHistories as $history)
                <div class="timeline-item">
                    <strong>{{ __('ui.status.'.$history->to_status) }}</strong>
                    <span>{{ $history->changed_at ? localized_datetime($history->changed_at) : __('ui.na') }}</span>
                    @if($history->display_comment)
                        <p style="margin:4px 0 0; font-size:14px; color:#4b5563;">{{ $history->display_comment }}</p>
                    @endif
                </div>
            @empty
                <p style="color:#6b7280;">{{ __('ui.track.no_history') }}</p>
            @endforelse
        </div>

        <p class="login-hint">
            {{ __('ui.track.login_hint') }}
            <a href="{{ route('login') }}">{{ __('ui.auth.login') }}</a>
        </p>
    </div>
</body>
</html>
