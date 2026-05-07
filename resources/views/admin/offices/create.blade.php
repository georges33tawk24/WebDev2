@extends('layouts.admin')

@section('title', 'Create Office')
@section('page-title', 'Create New Office')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Create New Office</div>
        <div class="page-subtitle">Add a new government office to the platform</div>
    </div>
    <a href="{{ route('admin.offices.index') }}" class="btn-secondary">← Back to Offices</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.offices.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Office Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Beirut Municipality Office">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Municipality</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality') }}" placeholder="e.g. Beirut">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="e.g. Downtown Beirut, Main Street">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}" placeholder="e.g. +961 1 234 567">
            @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email') }}" placeholder="e.g. office@municipality.gov.lb">
            @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Working Hours</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours') }}" placeholder="e.g. Mon-Fri 8:00AM - 4:00PM">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Create Office</button>
            <a href="{{ route('admin.offices.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection