@extends('layouts.admin')

@section('title', 'Analytics & Reports')
@section('page-title', 'Analytics & Reports')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Analytics & Reports</div>
        <div class="page-subtitle">Platform overview and statistics</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;">
            <svg width="20" height="20" fill="none" stroke="#1a56db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
        </div>
        <div class="stat-label">Total Offices</div>
        <div class="stat-number">{{ $totalOffices }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#d1fae5;">
            <svg width="20" height="20" fill="none" stroke="#065f46" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="stat-label">Total Users</div>
        <div class="stat-number">{{ $totalUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;">
            <svg width="20" height="20" fill="none" stroke="#5b21b6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="stat-label">Total Citizens</div>
        <div class="stat-number">{{ $totalCitizens }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;">
            <svg width="20" height="20" fill="none" stroke="#92400e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="stat-label">Total Staff</div>
        <div class="stat-number">{{ $totalStaff }}</div>
    </div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <div class="page-title" style="font-size:16px;">Offices Overview</div>
            <div class="page-subtitle">All government offices on the platform</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Office Name</th>
                    <th>Municipality</th>
                    <th>Contact Email</th>
                    <th>Contact Number</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offices as $office)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $office->name }}</td>
                    <td>{{ $office->municipality ?? '—' }}</td>
                    <td>{{ $office->contact_email ?? '—' }}</td>
                    <td>{{ $office->contact_number ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#6b7280; padding:32px;">No offices yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection