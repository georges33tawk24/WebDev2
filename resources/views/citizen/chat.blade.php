@extends('layouts.admin')

@section('title', 'Office Chat')
@section('page-title', 'Office Chat')

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        Chat with Office
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        Request: <strong>{{ $serviceRequest->reference_number }}</strong><br>
        Service: <strong>{{ $serviceRequest->service->name ?? 'N/A' }}</strong><br>
        Office: <strong>{{ $serviceRequest->office->name ?? 'N/A' }}</strong>
    </p>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:14px; border-radius:10px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:14px; border-radius:10px; margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px; margin-bottom:24px; max-height:420px; overflow-y:auto; background:#f9fafb;">
        @forelse($messages as $message)
            @php
                $isMine = $message->sender_id === auth()->id();
            @endphp

            <div style="display:flex; justify-content:{{ $isMine ? 'flex-end' : 'flex-start' }}; margin-bottom:14px;">
                <div style="max-width:70%; padding:12px 16px; border-radius:14px; background:{{ $isMine ? '#2563eb' : '#ffffff' }}; color:{{ $isMine ? '#ffffff' : '#111827' }}; border:1px solid #e5e7eb;">
                    <p style="font-size:13px; opacity:0.8; margin-bottom:6px;">
                        {{ $message->sender->name ?? 'User' }}
                    </p>

                    <p style="line-height:1.5;">
                        {{ $message->message }}
                    </p>

                    <p style="font-size:12px; opacity:0.7; margin-top:8px;">
                        {{ optional($message->created_at)->format('d M Y - h:i A') }}
                    </p>
                </div>
            </div>
        @empty
            <p style="color:#6b7280; text-align:center;">
                No messages yet. Start the conversation with the office.
            </p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('citizen.chat.send', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:16px;">
            <textarea name="message"
                      rows="4"
                      required
                      placeholder="Write your message to the office..."
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('message') }}</textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <a href="{{ route('citizen.requests') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                Back to Requests
            </a>

            <button type="submit" class="btn-primary">
                Send Message
            </button>
        </div>
    </form>
</div>
@endsection