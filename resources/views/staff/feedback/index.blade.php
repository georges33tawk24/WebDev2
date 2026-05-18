@extends('layouts.admin')

@section('title', __('ui.staff.feedback_title'))
@section('page-title', __('ui.staff.feedback_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.feedback_title') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.feedback_sub_view') }}</div>
    </div>
</div>

@forelse($feedback as $item)
<div class="card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div>
            <div style="font-size:15px; font-weight:600; color:#111827;">{{ $item->citizen?->name ?? __('ui.na') }}</div>
            <div style="font-size:12px; color:#6b7280; margin-top:2px;">{{ $item->created_at->format('M d, Y') }}</div>
        </div>
        <div style="display:flex; gap:4px;">
            @for($i = 1; $i <= 5; $i++)
                @if($i <= $item->rating)
                    <span style="color:#f59e0b; font-size:18px;">★</span>
                @else
                    <span style="color:#e5e7eb; font-size:18px;">★</span>
                @endif
            @endfor
        </div>
    </div>

    @if($item->comment)
    <div style="background:#f9fafb; border-radius:8px; padding:14px; margin-bottom:16px; font-size:14px; color:#374151; line-height:1.6;">
        {{ $item->comment }}
    </div>
    @endif

    {{-- Public Reply --}}
    @if($item->public_reply)
    <div style="background:#dbeafe; border-radius:8px; padding:14px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:600; color:#1e40af; margin-bottom:6px;">{{ strtoupper(__('ui.staff.public_reply')) }}</div>
        <div style="font-size:14px; color:#1e40af;">{{ $item->public_reply }}</div>
    </div>
    @endif

    {{-- Private Reply --}}
    @if($item->private_reply)
    <div style="background:#fef3c7; border-radius:8px; padding:14px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:600; color:#92400e; margin-bottom:6px;">{{ strtoupper(__('ui.staff.private_reply')) }}</div>
        <div style="font-size:14px; color:#92400e;">{{ $item->private_reply }}</div>
    </div>
    @endif

    {{-- Reply Form --}}
    <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:8px;">
        <div style="font-size:14px; font-weight:600; color:#111827; margin-bottom:12px;">{{ __('ui.staff.reply_to_feedback') }}</div>
        <form method="POST" action="{{ route('staff.feedback.reply', $item) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('ui.staff.reply_type') }}</label>
                <select name="reply_type" class="form-control" style="max-width:200px;">
                    <option value="public">{{ __('ui.staff.reply_public_option') }}</option>
                    <option value="private">{{ __('ui.staff.reply_private_option') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('ui.staff.your_reply') }}</label>
                <textarea name="reply" class="form-control" rows="3" placeholder="{{ __('ui.staff.reply_placeholder') }}"></textarea>
                @error('reply') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-primary">{{ __('ui.staff.send_reply') }}</button>
        </form>
    </div>
</div>
@empty
<div class="card" style="text-align:center; padding:48px; color:#6b7280;">
    <div style="font-size:48px; margin-bottom:16px;"></div>
    <div style="font-size:16px; font-weight:600; color:#111827; margin-bottom:8px;">{{ __('ui.staff.no_feedback_yet') }}</div>
    <div style="font-size:14px;">{{ __('ui.staff.no_feedback_sub') }}</div>
</div>
@endforelse

<div style="margin-top:20px;">
    {{ $feedback->links() }}
</div>
@endsection