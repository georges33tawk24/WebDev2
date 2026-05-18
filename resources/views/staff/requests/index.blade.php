@extends('layouts.staff')

@section('title', 'Service Requests')
@section('page-title', 'Service Requests')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Service Requests</div>
        <div class="page-subtitle">Manage all incoming citizen requests</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Citizen</th>
                <th>Service</th>
                <th>Office</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ substr($request->reference_number, 0, 8) }}...</td>
                <td>{{ $request->citizen?->name ?? '—' }}</td>
                <td>{{ $request->service?->name ?? '—' }}</td>
                <td>{{ $request->office?->name ?? '—' }}</td>
                <td style="color:#6b7280;">{{ $request->submitted_at?->format('M d, Y') ?? '—' }}</td>
                <td>
                    @php $status = $request->status; @endphp
                    <span class="badge badge-{{ str_replace('_', '-', $status) }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('staff.requests.show', $request) }}" class="btn-primary" style="padding:6px 12px; font-size:12px;">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:32px;">No requests yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $requests->links() }}
</div>
@endsection