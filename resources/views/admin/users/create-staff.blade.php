@extends('layouts.admin')

@section('title', __('ui.admin.create_staff'))
@section('page-title', __('ui.admin.create_staff'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.create_staff') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.create_staff_sub') }}</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">{{ __('ui.admin.back_users') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.users.staff.store') }}">
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
            <label class="form-label">{{ __('ui.admin.government_office') }}</label>
            <select name="office_id" class="form-control" required>
                <option value="">{{ __('ui.admin.select_an_office') }}</option>
                @foreach ($offices as $office)
                    <option value="{{ $office->id }}" @selected(old('office_id') == $office->id)>
                        {{ $office->localized('name') }}@if($office->municipality) — {{ $office->municipality }}@endif
                    </option>
                @endforeach
            </select>
            @error('office_id')<div class="form-error">{{ $message }}</div>@enderror
            @if ($offices->isEmpty())
                <p class="form-error" style="margin-top:8px;">
                    <a href="{{ route('admin.offices.create') }}">{{ __('ui.admin.create_office_before_staff') }}</a> {{ __('ui.admin.create_office_before_staff_hint') }}
                </p>
            @endif
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

        <button type="submit" class="btn-primary" @disabled($offices->isEmpty())>{{ __('ui.admin.create_staff_btn') }}</button>
    </form>
</div>
</x-form-page>
@endsection
