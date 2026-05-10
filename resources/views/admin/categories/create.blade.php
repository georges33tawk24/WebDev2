@extends('layouts.admin')

@section('title', 'Create Category')
@section('page-title', 'Create New Category')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Create New Category</div>
        <div class="page-subtitle">Add a new service category to the platform</div>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Back to Categories</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Civil Services">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Brief description of this category...">{{ old('description') }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Create Category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection