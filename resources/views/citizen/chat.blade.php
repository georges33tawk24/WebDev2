@extends('layouts.admin')

@section('title', __('ui.citizen.chat_title'))
@section('page-title', __('ui.citizen.chat_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.citizen.chat_with_office') }}</div>
        <div class="page-subtitle">
            {{ __('ui.citizen.reference_colon') }} {{ $serviceRequest->reference_number }}
            · {{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}
        </div>
    </div>
    <a href="{{ route('citizen.chats.index') }}" class="btn-secondary">{{ __('ui.citizen.back_to_chats') }}</a>
</div>

<x-chat-panel
    :service-request="$serviceRequest"
    :messages="$messages"
    :poll-url="route('api.chat.messages.index', $serviceRequest)"
    :send-url="route('api.chat.messages.store', $serviceRequest)"
    :empty-text="__('ui.citizen.chat_no_messages')"
    :back-url="route('citizen.chats.index')"
    :back-label="__('ui.citizen.back_to_chats')"
    :secondary-url="route('citizen.requests')"
    :secondary-label="__('ui.citizen.back_to_requests')"
    :placeholder="__('ui.citizen.chat_placeholder')"
/>
@endsection
