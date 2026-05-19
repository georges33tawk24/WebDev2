@extends('layouts.admin')

@section('title', __('ui.admin.request_details'))
@section('page-title', __('ui.admin.request_details'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.request_details') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.reference_label', ['ref' => $serviceRequest->reference_number]) }}</div>
    </div>
    <a href="{{ route('admin.requests.index') }}" class="btn-secondary">{{ __('ui.admin.back_requests') }}</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <table style="width:100%;">
        <tr>
            <td style="padding:8px 0; color:#6b7280; width:35%;">{{ __('ui.table.citizen') }}</td>
            <td>{{ $serviceRequest->citizen?->name ?? __('ui.na') }} ({{ $serviceRequest->citizen?->email ?? __('ui.na') }})</td>
        </tr>
        <tr>
            <td style="padding:8px 0; color:#6b7280;">{{ __('ui.table.office') }}</td>
            <td>{{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 0; color:#6b7280;">{{ __('ui.table.service') }}</td>
            <td>{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 0; color:#6b7280;">{{ __('ui.staff.current_status') }}</td>
            <td><x-status-badge :status="$serviceRequest->status" /></td>
        </tr>
        <tr>
            <td style="padding:8px 0; color:#6b7280; vertical-align:top;">{{ __('ui.staff.payment_status') }}</td>
            <td><x-request-payment-status :service-request="$serviceRequest" /></td>
        </tr>
    </table>
</div>

<div class="card">
    <h3 style="margin:0 0 16px; font-size:16px;">{{ __('ui.staff.status_history') }}</h3>
    @forelse($serviceRequest->statusHistories as $history)
        <div style="padding:10px 0; border-bottom:1px solid #e5e7eb;">
            <strong>{{ __('ui.status.'.$history->to_status) }}</strong>
            <span style="color:#6b7280; font-size:13px; margin-left:8px;">{{ $history->changed_at ? localized_datetime($history->changed_at) : __('ui.na') }}</span>
            @if($history->display_comment)<p style="margin:6px 0 0; color:#4b5563;">{{ $history->display_comment }}</p>@endif
        </div>
    @empty
        <p style="color:#6b7280;">{{ __('ui.staff.no_status_changes') }}</p>
    @endforelse
</div>
@endsection
