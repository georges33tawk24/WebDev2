@extends('layouts.admin')

@section('title', __('ui.admin.add_citizen'))
@section('page-title', __('ui.admin.add_citizen'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.add_citizen') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.add_citizen_sub') }}</div>
    </div>
    <a href="{{ route('admin.citizens.index') }}" class="btn-secondary">{{ __('ui.admin.back_citizens') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.users.citizens.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ __('ui.auth.full_name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.auth.email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.phone') }}</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            @error('phone')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.auth.password') }}</label>
            <input type="password" name="password" class="form-control" required autocomplete="new-password">
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.auth.confirm_password') }}</label>
            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-primary">{{ __('ui.admin.create_citizen_btn') }}</button>
    </form>
</div>
</x-form-page>
@endsection
