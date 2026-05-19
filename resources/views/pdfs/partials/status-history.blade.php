@php
    $histories = $serviceRequest->statusHistories ?? collect();
@endphp

@if($histories->isNotEmpty())
    <div style="margin-top: 28px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
        <h3 style="font-size: 14px; color: #1e429f; margin-bottom: 12px;">{{ __('ui.staff.status_history') }}</h3>
        @foreach($histories as $history)
            <div style="font-size: 12px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6;">
                <strong>{{ __('ui.status.'.$history->to_status) }}</strong>
                <span style="color: #6b7280;"> — {{ $history->changed_at ? localized_datetime($history->changed_at) : __('ui.na') }}</span>
                @if($history->display_comment)
                    <div style="color: #4b5563; margin-top: 4px;">{{ $history->display_comment }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
