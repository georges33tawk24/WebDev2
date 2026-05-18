@extends('layouts.staff')

@section('title', 'Staff Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Welcome, {{ auth()->user()->name }}!</div>
        <div class="page-subtitle">{{ auth()->user()->office?->name ?? 'No office assigned' }}</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        
        <div class="stat-label">Total Requests</div>
        <div class="stat-number">{{ $totalRequests }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Pending Requests</div>
        <div class="stat-number">{{ $pendingRequests }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Approved Requests</div>
        <div class="stat-number">{{ $approvedRequests }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Completed Requests</div>
        <div class="stat-number">{{ $completedRequests }}</div>
    </div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <div class="page-title" style="font-size:16px;">Recent Requests</div>
            <div class="page-subtitle">Latest incoming requests for your office</div>
        </div>
        <a href="{{ route('staff.requests.index') }}" class="btn-primary">View All</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Citizen</th>
                    <th>Service</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRequests as $request)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ substr($request->reference_number, 0, 8) }}...</td>
                    <td>{{ $request->citizen?->name ?? '—' }}</td>
                    <td>{{ $request->service?->name ?? '—' }}</td>
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
                    <td colspan="6" style="text-align:center; color:#6b7280; padding:32px;">No requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection