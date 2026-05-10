@extends('layouts.admin')

@section('title', 'Service Categories')
@section('page-title', 'Service Categories')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Service Categories</div>
        <div class="page-subtitle">Manage all service categories on the platform</div>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">Add New Category</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Description</th>
                <th>Total Services</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr>
                <td style="font-weight:600; color:#111827;">{{ $category->name }}</td>
                <td>{{ $category->description ?? '—' }}</td>
                <td>
                    <span class="badge" style="background:#dbeafe; color:#1e40af;">
                        {{ $category->services_count }} services
                    </span>
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Edit</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; color:#6b7280; padding:32px;">
                    No categories yet. <a href="{{ route('admin.categories.create') }}" style="color:#1a56db;">Create one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $categories->links() }}
</div>
@endsection