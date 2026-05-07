@extends('layouts.admin')

@section('title', 'Citizens')
@section('page-title', 'Citizens')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Registered Citizens</div>
        <div class="page-subtitle">List of all citizens registered on the platform</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citizens as $citizen)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $citizen->name }}</td>
                <td>{{ $citizen->email }}</td>
                <td>{{ $citizen->phone ?? '—' }}</td>
                <td>
                    @if($citizen->email_verified_at)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </td>
                <td style="color:#6b7280;">{{ $citizen->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">No citizens found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $citizens->links() }}
</div>
@endsection