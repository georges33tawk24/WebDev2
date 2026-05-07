@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
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
            <svg width="20" height="20" fill="none" stroke="#92400e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="stat-label">Active Staff Accounts</div>
        <div class="stat-number">{{ $totalStaff }}</div>
    </div>
</div>

{{-- Recent Offices --}}
<div class="card" style="margin-bottom: 24px;">
    <div class="page-header" style="margin-bottom: 16px;">
        <div>
            <div class="page-title" style="font-size:16px;">Recent Government Offices</div>
            <div class="page-subtitle">Latest offices added to the platform</div>
        </div>
        <a href="{{ route('admin.offices.index') }}" class="btn-primary">View All</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Office Name</th>
                    <th>Municipality</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOffices as $office)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $office->name }}</td>
                    <td>{{ $office->municipality ?? '—' }}</td>
                    <td>{{ $office->email ?? '—' }}</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>
                        <a href="{{ route('admin.offices.edit', $office) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">No offices yet. <a href="{{ route('admin.offices.create') }}" style="color:#1a56db;">Create one →</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Users --}}
<div class="card">
    <div class="page-header" style="margin-bottom: 16px;">
        <div>
            <div class="page-title" style="font-size:16px;">Recent User Accounts</div>
            <div class="page-subtitle">Latest registered users on the platform</div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-primary">View All</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $user)
                <tr>
                    <td style="font-weight:600; color:#111827;">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge" style="background:#f3f4f6; color:#374151;">{{ ucfirst(str_replace('_', ' ', $user->role?->slug ?? 'N/A')) }}</span></td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td style="color:#6b7280;">{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection