@extends('layouts.admin')

@section('title', 'Add Office Staff')
@section('page-title', 'Add Office Staff')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Add Office Staff</div>
        <div class="page-subtitle">Create a municipality staff account linked to an office</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back to Users</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('admin.users.staff.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Full name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Government office</label>
            <select name="office_id" class="form-control" required>
                <option value="">Select an office</option>
                @foreach ($offices as $office)
                    <option value="{{ $office->id }}" @selected(old('office_id') == $office->id)>
                        {{ $office->name }}@if($office->municipality) — {{ $office->municipality }}@endif
                    </option>
                @endforeach
            </select>
            @error('office_id')<div class="form-error">{{ $message }}</div>@enderror
            @if ($offices->isEmpty())
                <p class="form-error" style="margin-top:8px;">
                    <a href="{{ route('admin.offices.create') }}">Create an office</a> before adding staff.
                </p>
            @endif
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="new-password">
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-primary" @disabled($offices->isEmpty())>Create staff account</button>
    </form>
</div>
@endsection
