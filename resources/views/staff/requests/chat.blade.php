@extends('layouts.admin')

@section('title', 'Request Chat')
@section('page-title', 'Request Chat')

@section('content')

<div class="card" style="max-width:1100px; margin:auto;">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; margin-bottom:28px; flex-wrap:wrap;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">
                Citizen Communication Center
            </h1>

            <p style="color:#6b7280; line-height:1.7; max-width:700px;">
                Reply directly to the citizen regarding this service request.
                All messages are stored with the request for transparency and follow-up.
            </p>
        </div>

        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:16px; padding:18px 20px; min-width:300px;">
            <div style="margin-bottom:12px;">
                <div style="font-size:13px; color:#6b7280;">Reference Number</div>
                <div style="font-weight:700;">{{ $serviceRequest->reference_number }}</div>
            </div>

            <div style="margin-bottom:12px;">
                <div style="font-size:13px; color:#6b7280;">Citizen</div>
                <div style="font-weight:700;">{{ $serviceRequest->citizen->name ?? 'N/A' }}</div>
            </div>

            <div>
                <div style="font-size:13px; color:#6b7280;">Service</div>
                <div style="font-weight:700;">{{ $serviceRequest->service->name ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; border:1px solid #bbf7d0; margin-bottom:22px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:12px; border:1px solid #fecaca; margin-bottom:22px;">
            {{ session('error') }}
        </div>
    @endif

    <div id="chatContainer"
         style="border:1px solid #e5e7eb; border-radius:22px; padding:24px; background:#f9fafb; height:520px; overflow-y:auto; margin-bottom:26px;">

        @forelse($messages as $message)
            @php
                $isMine = $message->sender_id === auth()->id();
            @endphp

            <div style="display:flex; justify-content:{{ $isMine ? 'flex-end' : 'flex-start' }}; margin-bottom:22px;">
                <div style="
                    max-width:72%;
                    background:{{ $isMine ? '#2563eb' : '#ffffff' }};
                    color:{{ $isMine ? '#ffffff' : '#111827' }};
                    border-radius:20px;
                    padding:18px 20px;
                    border:{{ $isMine ? 'none' : '1px solid #e5e7eb' }};
                    box-shadow:0 3px 10px rgba(0,0,0,0.04);
                ">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:10px; flex-wrap:wrap;">
                        <div style="font-size:13px; font-weight:700; opacity:0.9;">
                            {{ $message->sender->name ?? 'User' }}
                            @if($isMine)
                                • You
                            @endif
                        </div>

                        <div style="font-size:12px; opacity:0.75;">
                            {{ optional($message->created_at)->format('d M Y - h:i A') }}
                        </div>
                    </div>

                    <div style="line-height:1.8; font-size:15px; word-break:break-word;">
                        {{ $message->message }}
                    </div>
                </div>
            </div>
        @empty
            <div style="height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; color:#6b7280;">
                <div style="font-size:54px; margin-bottom:18px;">💬</div>

                <h2 style="font-size:24px; margin-bottom:10px; color:#111827;">
                    No Messages Yet
                </h2>

                <p style="max-width:520px; line-height:1.8;">
                    No conversation has started for this request yet.
                    You can send the first message to ask for clarification,
                    request missing documents, or provide guidance.
                </p>
            </div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('staff.requests.chat.send', $serviceRequest) }}">
        @csrf

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; padding:22px;">
            <label style="display:block; font-size:15px; font-weight:700; margin-bottom:12px;">
                Send Reply
            </label>

            <textarea
                name="message"
                rows="5"
                required
                placeholder="Write your reply to the citizen..."
                style="width:100%; border:1px solid #d1d5db; border-radius:14px; padding:16px; font-size:15px; resize:vertical; outline:none; line-height:1.7;"
            >{{ old('message') }}</textarea>

            @error('message')
                <div style="color:#dc2626; margin-top:10px; font-size:14px;">
                    {{ $message }}
                </div>
            @enderror

            <div style="display:flex; justify-content:space-between; align-items:center; gap:14px; margin-top:18px; flex-wrap:wrap;">
                <a href="{{ route('staff.requests.show', $serviceRequest) }}"
                   class="btn-secondary"
                   style="text-decoration:none;">
                    Back to Request
                </a>

                <button type="submit" class="btn-primary">
                    Send Reply
                </button>
            </div>
        </div>
    </form>

</div>

<script type="module">
    const chatContainer = document.getElementById('chatContainer');

    function appendLiveMessage(event) {
        const isMine = Number(event.sender_id) === Number({{ auth()->id() }});

        const wrapper = document.createElement('div');
        wrapper.style.display = 'flex';
        wrapper.style.justifyContent = isMine ? 'flex-end' : 'flex-start';
        wrapper.style.marginBottom = '22px';

        wrapper.innerHTML = `
            <div style="
                max-width:72%;
                background:${isMine ? '#2563eb' : '#ffffff'};
                color:${isMine ? '#ffffff' : '#111827'};
                border-radius:20px;
                padding:18px 20px;
                border:${isMine ? 'none' : '1px solid #e5e7eb'};
                box-shadow:0 3px 10px rgba(0,0,0,0.04);
            ">
                <div style="font-size:13px; font-weight:700; opacity:0.9; margin-bottom:10px;">
                    ${event.sender_name}${isMine ? ' • You' : ''}
                </div>

                <div style="line-height:1.8; font-size:15px; word-break:break-word;">
                    ${event.message}
                </div>

                <div style="font-size:12px; opacity:0.75; margin-top:10px;">
                    ${event.created_at}
                </div>
            </div>
        `;

        chatContainer.appendChild(wrapper);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    if (chatContainer && window.Echo) {
        window.Echo.channel('request-chat.{{ $serviceRequest->id }}')
            .listen('.message.sent', (event) => {
                appendLiveMessage(event);
            });
    }

    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
</script>

@endsection