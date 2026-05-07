@extends('layouts.admin')

@section('title', 'Government Offices')
@section('page-title', 'Government Offices')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Government Offices</div>
        <div class="page-subtitle">Manage all government offices on the platform</div>
    </div>
    <a href="{{ route('admin.offices.create') }}" class="btn-primary">
        + Add New Office
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Office Name</th>
                <th>Municipality</th>
                <th>Contact Number</th>
                <th>Contact Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($offices as $office)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $office->name }}</td>
                <td>{{ $office->municipality ?? '—' }}</td>
                <td>{{ $office->contact_number ?? '—' }}</td>
                <td>{{ $office->contact_email ?? '—' }}</td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.offices.edit', $office) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Edit</a>
                    <form method="POST" action="{{ route('admin.offices.destroy', $office) }}" onsubmit="return confirm('Delete this office?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">
                    No offices yet. <a href="{{ route('admin.offices.create') }}" style="color:#1a56db;">Create one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $offices->links() }}
</div>
@endsection