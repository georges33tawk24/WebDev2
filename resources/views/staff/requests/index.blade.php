@extends('layouts.admin')

@section('title', __('ui.staff.requests_title'))
@section('page-title', __('ui.staff.requests_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.requests_title') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.requests_sub_incoming') }}</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.table.reference') }}</th>
                <th>{{ __('ui.table.citizen') }}</th>
                <th>{{ __('ui.table.service') }}</th>
                <th>{{ __('ui.table.office') }}</th>
                <th>{{ __('ui.table.submitted') }}</th>
                <th>{{ __('ui.table.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ substr($request->reference_number, 0, 8) }}...</td>
                <td>{{ $request->citizen?->name ?? __('ui.na') }}</td>
                <td>{{ $request->service?->localized('name') ?? __('ui.na') }}</td>
                <td>{{ $request->office?->localized('name') ?? __('ui.na') }}</td>
                <td style="color:#6b7280;">{{ $request->submitted_at?->format('M d, Y') ?? __('ui.na') }}</td>
                <td>
                    <x-status-badge :status="$request->status" />
                </td>
                <td>
                    <a href="{{ route('staff.requests.show', $request) }}" class="btn-primary" style="padding:6px 12px; font-size:12px;">{{ __('ui.view') }}</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.staff.no_requests') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $requests->links() }}
</div>
@endsection