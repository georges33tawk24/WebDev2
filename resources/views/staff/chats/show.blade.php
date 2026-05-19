@extends('layouts.admin')

@section('title', __('ui.staff.chat_title'))
@section('page-title', __('ui.staff.chat_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.chat_with_citizen') }}</div>
        <div class="page-subtitle">
            {{ __('ui.staff.reference_label', ['ref' => $serviceRequest->reference_number]) }}
            · {{ $serviceRequest->citizen?->name ?? __('ui.na') }}
        </div>
    </div>
    <a href="{{ route('staff.chats.index') }}" class="btn-secondary">{{ __('ui.staff.back_chats') }}</a>
</div>

<p style="color:#6b7280; margin-bottom:16px;">
    {{ __('ui.table.service') }}:
    <strong>{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</strong>
</p>

<x-chat-panel
    :service-request="$serviceRequest"
    :messages="$messages"
    :poll-url="route('api.chat.messages.index', $serviceRequest)"
    :send-url="route('api.chat.messages.store', $serviceRequest)"
    :empty-text="__('ui.staff.chat_no_messages')"
    :back-url="route('staff.chats.index')"
    :back-label="__('ui.staff.back_chats')"
    :secondary-url="route('staff.requests.show', $serviceRequest)"
    :secondary-label="__('ui.staff.view_request')"
/>
@endsection
