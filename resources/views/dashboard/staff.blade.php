@extends('layouts.admin')

@section('title', __('ui.staff.dashboard'))
@section('page-title', __('ui.dashboard_overview'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{!! __('ui.staff.welcome', ['name' => '<bdi>'.e(auth()->user()->name).'</bdi>']) !!}</div>
        <div class="page-subtitle">{{ auth()->user()->office?->localized('name') ?? __('ui.no_office_assigned') }}</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.staff.total_requests') }}</div>
        <div class="stat-number">{{ localized_number($totalRequests) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.staff.pending_requests') }}</div>
        <div class="stat-number">{{ localized_number($pendingRequests) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.staff.approved_requests') }}</div>
        <div class="stat-number">{{ localized_number($approvedRequests) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">{{ __('ui.staff.completed_requests') }}</div>
        <div class="stat-number">{{ localized_number($completedRequests) }}</div>
    </div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <div class="page-title" style="font-size:16px;">{{ __('ui.staff.recent_requests') }}</div>
            <div class="page-subtitle">{{ __('ui.staff.recent_requests_sub') }}</div>
        </div>
        <a href="{{ route('staff.requests.index') }}" class="btn-primary">{{ __('ui.view_all') }}</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.reference') }}</th>
                    <th>{{ __('ui.table.citizen') }}</th>
                    <th>{{ __('ui.table.service') }}</th>
                    <th>{{ __('ui.table.submitted') }}</th>
                    <th>{{ __('ui.table.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRequests as $request)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ substr($request->reference_number, 0, 8) }}...</td>
                    <td>{{ $request->citizen?->name ?? __('ui.na') }}</td>
                    <td>{{ $request->service?->name ?? __('ui.na') }}</td>
                    <td style="color:#6b7280;">{{ $request->submitted_at ? localized_date($request->submitted_at, 'M d, Y') : __('ui.na') }}</td>
                    <td><x-status-badge :status="$request->status" /></td>
                    <td>
                        <a href="{{ route('staff.requests.show', $request) }}" class="btn-primary" style="padding:6px 12px; font-size:12px;">{{ __('ui.view') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.staff.no_requests') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
