@extends('layouts.admin')

@section('title', 'Edit Office')
@section('page-title', 'Edit Office')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Edit Office</div>
        <div class="page-subtitle">Update the details of {{ $office->name }}</div>
    </div>
    <a href="{{ route('admin.offices.index') }}" class="btn-secondary">← Back to Offices</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.offices.update', $office) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Office Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Municipality</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $office->municipality) }}">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $office->contact_number) }}">
            @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $office->contact_email) }}">
            @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Working Hours</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours', $office->working_hours) }}">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Update Office</button>
            <a href="{{ route('admin.offices.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection