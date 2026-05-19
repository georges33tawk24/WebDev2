@extends('layouts.admin')

@section('title', __('ui.staff.appointments_title'))
@section('page-title', __('ui.staff.appointments_title'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.appointments_title') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.appointments_sub') }}</div>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.table.citizen') }}</th>
                    <th>{{ __('ui.table.service') }}</th>
                    <th>{{ __('ui.staff.appointment_when') }}</th>
                    <th>{{ __('ui.table.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->citizen?->name ?? __('ui.na') }}</td>
                        <td>{{ $appointment->serviceRequest?->service?->localized('name') ?? __('ui.na') }}</td>
                        <td>{{ localized_datetime($appointment->starts_at) }}</td>
                        <td><x-status-badge :status="$appointment->status" /></td>
                        <td>
                            <form method="POST" action="{{ route('staff.appointments.updateStatus', $appointment) }}" style="display:flex; gap:8px; align-items:center;">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-control" style="width:auto; padding:6px 10px; font-size:12px;">
                                    @foreach(['scheduled','completed','cancelled','rescheduled'] as $status)
                                        <option value="{{ $status }}" @selected($appointment->status === $status)>{{ __('ui.status.'.$status) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary" style="padding:6px 12px; font-size:12px;">{{ __('ui.save') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">{{ __('ui.staff.appointments_empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($appointments->hasPages())
        <div style="margin-top:16px;">{{ $appointments->links('vendor.pagination.eservices') }}</div>
    @endif
</div>
@endsection
