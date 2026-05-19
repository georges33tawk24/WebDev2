@extends('layouts.admin')

@section('title', __('ui.citizen.chats_title'))
@section('page-title', __('ui.citizen.chats_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.citizen.chats_title') }}</div>
        <div class="page-subtitle">{{ __('ui.citizen.chats_sub') }}</div>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.reference') }}</th>
                    <th>{{ __('ui.table.office') }}</th>
                    <th>{{ __('ui.table.service') }}</th>
                    <th>{{ __('ui.staff.last_message') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>
                            {{ $request->reference_number }}
                            @if ($request->unread_count > 0)
                                <span class="chat-unread-pill">{{ localized_digits($request->unread_count) }}</span>
                            @endif
                        </td>
                        <td>{{ $request->office?->localized('name') ?? __('ui.na') }}</td>
                        <td>{{ $request->service?->localized('name') ?? __('ui.na') }}</td>
                        <td style="color:#6b7280;">
                            {{ $request->last_message_at ? localized_datetime($request->last_message_at) : __('ui.na') }}
                        </td>
                        <td>
                            <a href="{{ route('citizen.chat', $request) }}" class="btn-primary" style="padding:6px 12px; font-size:12px;">
                                {{ __('ui.staff.open_chat') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">
                            {{ __('ui.citizen.chats_empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($requests->hasPages())
        <div style="margin-top:16px;">
            {{ $requests->links('vendor.pagination.eservices') }}
        </div>
    @endif
</div>
@endsection
