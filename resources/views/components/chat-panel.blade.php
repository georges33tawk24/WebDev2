@props([
    'serviceRequest',
    'messages',
    'pollUrl',
    'sendUrl',
    'emptyText',
    'backUrl' => null,
    'backLabel' => null,
    'secondaryUrl' => null,
    'secondaryLabel' => null,
    'placeholder' => null,
])

@php
    $lastMessageId = $messages->last()?->id ?? 0;
    $placeholder = $placeholder ?? __('ui.staff.chat_placeholder');
@endphp

<div class="card" data-live-chat>
    {{ $header ?? '' }}

    <div id="chat-thread" class="chat-thread" aria-live="polite">
        @forelse($messages as $chatMessage)
            @php($isMine = (int) $chatMessage->sender_id === (int) auth()->id())
            <div class="chat-bubble-row {{ $isMine ? 'chat-bubble-row--mine' : 'chat-bubble-row--theirs' }}" data-message-id="{{ $chatMessage->id }}">
                <div class="chat-bubble {{ $isMine ? 'chat-bubble--mine' : 'chat-bubble--theirs' }}">
                    <p class="chat-bubble__author">{{ $chatMessage->sender?->name ?? __('ui.na') }}</p>
                    <p class="chat-bubble__text">{{ $chatMessage->message }}</p>
                    <p class="chat-bubble__time">{{ $chatMessage->created_at ? localized_datetime($chatMessage->created_at) : __('ui.na') }}</p>
                </div>
            </div>
        @empty
            <p id="chat-thread-empty" class="chat-thread__empty">{{ $emptyText }}</p>
        @endforelse
    </div>

    <form id="chat-form" class="chat-reply-form" action="{{ $sendUrl }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="chat-message-input">{{ $placeholder }}</label>
            <textarea id="chat-message-input" name="message" class="form-control" rows="3" required placeholder="{{ $placeholder }}"></textarea>
            <div id="chat-form-error" class="form-error" hidden></div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            @if($backUrl)
                <a href="{{ $backUrl }}" class="btn-secondary" style="text-decoration:none;">{{ $backLabel ?? __('ui.back') }}</a>
            @endif
            @if($secondaryUrl)
                <a href="{{ $secondaryUrl }}" class="btn-secondary" style="text-decoration:none;">{{ $secondaryLabel }}</a>
            @endif
            <button type="submit" class="btn-primary">{{ __('ui.staff.send_message') }}</button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.__CHAT_CONFIG__ = {
            pollUrl: @json($pollUrl),
            sendUrl: @json($sendUrl),
            csrf: @json(csrf_token()),
            currentUserId: @json(auth()->id()),
            lastMessageId: @json($lastMessageId),
        };
    </script>
    @vite('resources/js/chat-page.js')
@endpush
