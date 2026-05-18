@extends('layouts.staff')

@section('title', 'Citizen Feedback')
@section('page-title', 'Citizen Feedback')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Citizen Feedback</div>
        <div class="page-subtitle">View and respond to citizen ratings and comments</div>
    </div>
</div>

@forelse($feedback as $item)
<div class="card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div>
            <div style="font-size:15px; font-weight:600; color:#111827;">{{ $item->citizen?->name ?? '—' }}</div>
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
        <div style="font-size:11px; font-weight:600; color:#1e40af; margin-bottom:6px;">PUBLIC REPLY</div>
        <div style="font-size:14px; color:#1e40af;">{{ $item->public_reply }}</div>
    </div>
    @endif

    {{-- Private Reply --}}
    @if($item->private_reply)
    <div style="background:#fef3c7; border-radius:8px; padding:14px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:600; color:#92400e; margin-bottom:6px;">PRIVATE REPLY</div>
        <div style="font-size:14px; color:#92400e;">{{ $item->private_reply }}</div>
    </div>
    @endif

    {{-- Reply Form --}}
    <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:8px;">
        <div style="font-size:14px; font-weight:600; color:#111827; margin-bottom:12px;">Reply to this feedback</div>
        <form method="POST" action="{{ route('staff.feedback.reply', $item) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reply Type</label>
                <select name="reply_type" class="form-control" style="max-width:200px;">
                    <option value="public">Public (visible to everyone)</option>
                    <option value="private">Private (only citizen sees it)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Your Reply</label>
                <textarea name="reply" class="form-control" rows="3" placeholder="Write your reply..."></textarea>
                @error('reply') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-primary">Send Reply</button>
        </form>
    </div>
</div>
@empty
<div class="card" style="text-align:center; padding:48px; color:#6b7280;">
    <div style="font-size:48px; margin-bottom:16px;"></div>
    <div style="font-size:16px; font-weight:600; color:#111827; margin-bottom:8px;">No feedback yet</div>
    <div style="font-size:14px;">Citizen feedback will appear here once they rate your services.</div>
</div>
@endforelse

<div style="margin-top:20px;">
    {{ $feedback->links() }}
</div>
@endsection