@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Edit Category</div>
        <div class="page-subtitle">Update the details of {{ $category->name }}</div>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary"> Back to Categories</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Update Category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection