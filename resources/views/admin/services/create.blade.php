@extends('layouts.admin')

@section('title', 'Create Service')
@section('page-title', 'Create New Service')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Create New Service</div>
        <div class="page-subtitle">Add a new service to the platform</div>
    </div>
    <a href="{{ route('admin.services.index') }}" class="btn-secondary">Back to Services</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Office </label>
            <select name="office_id" class="form-control">
                <option value="">Select an office...</option>
                @foreach($offices as $office)
                    <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                        {{ $office->name }}
                    </option>
                @endforeach
            </select>
            @error('office_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <option value="">Select a category...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Service Name </label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Birth Certificate">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Brief description of this service...">{{ old('description') }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Price ($) </label>
                <input type="number" name="price" class="form-control" value="{{ old('price', 0) }}" min="0" step="0.01">
                @error('price') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Estimated Duration (minutes)</label>
                <input type="number" name="estimated_duration_minutes" class="form-control" value="{{ old('estimated_duration_minutes') }}" min="1" placeholder="e.g. 30">
                @error('estimated_duration_minutes') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Required Documents</label>
            <input type="text" name="required_documents" class="form-control" value="{{ old('required_documents') }}" placeholder="e.g. ID Card, Birth Certificate, Passport (comma separated)">
            <small style="color:#6b7280; font-size:12px;">Separate multiple documents with commas</small>
            @error('required_documents') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="form-label" style="margin:0;">Active</span>
            </label>
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Create Service</button>
            <a href="{{ route('admin.services.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection