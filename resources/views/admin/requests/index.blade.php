@extends('layouts.admin')

@section('title', __('ui.admin.requests_title'))
@section('page-title', __('ui.admin.requests_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.requests_title') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.requests_sub') }}</div>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.reference') }}</th>
                    <th>{{ __('ui.table.citizen') }}</th>
                    <th>{{ __('ui.table.office') }}</th>
                    <th>{{ __('ui.table.service') }}</th>
                    <th>{{ __('ui.table.status') }}</th>
                    <th>{{ __('ui.table.submitted') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td>{{ $request->reference_number }}</td>
                        <td>{{ $request->citizen?->name ?? __('ui.na') }}</td>
                        <td>{{ $request->office?->localized('name') ?? __('ui.na') }}</td>
                        <td>{{ $request->service?->localized('name') ?? __('ui.na') }}</td>
                        <td><x-status-badge :status="$request->status" /></td>
                        <td>{{ $request->submitted_at ? localized_datetime($request->submitted_at) : __('ui.na') }}</td>
                        <td>
                            <a href="{{ route('admin.requests.show', $request) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">
                                {{ __('ui.view') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($requests->hasPages())
        <div style="margin-top:16px;">{{ $requests->links('vendor.pagination.eservices') }}</div>
    @endif
</div>
@endsection
