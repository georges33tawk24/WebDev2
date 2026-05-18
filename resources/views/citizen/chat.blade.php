@extends('layouts.admin')

@section('title', __('ui.citizen.chat_title'))
@section('page-title', __('ui.citizen.chat_title'))

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        {{ __('ui.citizen.chat_with_office') }}
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        {{ __('ui.citizen.reference_colon') }} <strong>{{ $serviceRequest->reference_number }}</strong><br>
        {{ __('ui.table.service') }}: <strong>{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</strong><br>
        {{ __('ui.citizen.office_colon') }} <strong>{{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</strong>
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
                        {{ $message->sender->name ?? __('ui.citizen.portal') }}
                    </p>

                    <p style="line-height:1.5;">
                        {{ $message->message }}
                    </p>

                    <p style="font-size:12px; opacity:0.7; margin-top:8px;">
                        {{ $message->created_at ? localized_datetime($message->created_at) : __('ui.na') }}
                    </p>
                </div>
            </div>
        @empty
            <p style="color:#6b7280; text-align:center;">
                {{ __('ui.citizen.chat_no_messages') }}
            </p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('citizen.chat.send', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:16px;">
            <textarea name="message"
                      rows="4"
                      required
                      placeholder="{{ __('ui.citizen.chat_placeholder') }}"
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('message') }}</textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <a href="{{ route('citizen.requests') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                {{ __('ui.citizen.back_to_requests') }}
            </a>

            <button type="submit" class="btn-primary">
                {{ __('ui.citizen.send_message') }}
            </button>
        </div>
    </form>
</div>
@endsection
