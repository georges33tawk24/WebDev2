@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        
        <div class="stat-label">Total Offices</div>
        <div class="stat-number">{{ $totalOffices }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Total Users</div>
        <div class="stat-number">{{ $totalUsers }}</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-label">Total Citizens</div>
        <div class="stat-number">{{ $totalCitizens }}</div>
    </div>
    <div class="stat-card">
        
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