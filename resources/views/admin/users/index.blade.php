@extends('layouts.admin')

@section('title', 'Users & Accounts')
@section('page-title', 'Users & Accounts')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Users & Accounts</div>
        <div class="page-subtitle">Manage all user accounts on the platform</div>
    </div>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge" style="background:#f3f4f6; color:#374151;">
                        {{ ucfirst(str_replace('_', ' ', $user->role?->slug ?? 'N/A')) }}
                    </span>
                </td>
                <td>
                    @if($user->email_verified_at)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </td>
                <td style="color:#6b7280;">{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="{{ $user->email_verified_at ? 'btn-danger' : 'btn-primary' }}" style="padding:6px 12px; font-size:12px;">
                            {{ $user->email_verified_at ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#6b7280; padding:32px;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $users->links() }}
</div>
@endsection