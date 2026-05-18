@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:28px;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">
                Notification Center
            </h1>
            <p style="color:#6b7280; line-height:1.7;">
                View system alerts, request updates, chat messages, appointment reminders, and payment notifications.
            </p>
        </div>

        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="btn-secondary">
                Mark All as Read
            </button>
        </form>
    </div>

    @forelse($notifications as $notification)
        <div style="
            border:1px solid {{ $notification->is_read ? '#e5e7eb' : '#93c5fd' }};
            background:{{ $notification->is_read ? '#ffffff' : '#eff6ff' }};
            border-radius:18px;
            padding:20px;
            margin-bottom:16px;
        ">
            <div style="display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap;">
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                        <h2 style="font-size:18px; font-weight:700; margin:0;">
                            {{ $notification->title }}
                        </h2>

                        @if(!$notification->is_read)
                            <span style="background:#2563eb; color:white; padding:4px 9px; border-radius:999px; font-size:12px; font-weight:700;">
                                New
                            </span>
                        @endif
                    </div>

                    <p style="color:#374151; line-height:1.7; margin-bottom:10px;">
                        {{ $notification->message }}
                    </p>

                    <div style="font-size:13px; color:#6b7280;">
                        Type: {{ ucwords(str_replace('_', ' ', $notification->type)) }}
                        • {{ $notification->created_at->format('d M Y - h:i A') }}
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; min-width:160px;">
                    @if($notification->url)
                        <a href="{{ $notification->url }}" class="btn-primary" style="text-decoration:none; justify-content:center;">
                            Open
                        </a>
                    @endif

                    @if(!$notification->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="btn-secondary" style="width:100%; justify-content:center;">
                                Mark Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:70px 20px; color:#6b7280;">
            <h2 style="font-size:24px; margin-bottom:10px;">No Notifications Yet</h2>
            <p>When important system events happen, they will appear here.</p>
        </div>
    @endforelse

    <div style="margin-top:24px;">
        {{ $notifications->links() }}
    </div>
</div>
@endsection