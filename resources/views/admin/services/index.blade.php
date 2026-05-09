@extends('layouts.admin')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Services</div>
        <div class="page-subtitle">Manage all services on the platform</div>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn-primary">Add New Service</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Category</th>
                <th>Office</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $service->name }}</td>
                <td>{{ $service->category?->name ?? '—' }}</td>
                <td>{{ $service->office?->name ?? '—' }}</td>
                <td>${{ number_format($service->price, 2) }}</td>
                <td>{{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' mins' : '—' }}</td>
                <td>
                    @if($service->is_active)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.services.edit', $service) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Edit</a>
                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:32px;">
                    No services yet. <a href="{{ route('admin.services.create') }}" style="color:#1a56db;">Create one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $services->links() }}
</div>
@endsection