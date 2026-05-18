@extends('layouts.admin')

@section('title', __('ui.admin.edit_office'))
@section('page-title', __('ui.admin.edit_office'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.edit_office') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.edit_office_sub', ['name' => $office->name]) }}</div>
    </div>
    <a href="{{ route('admin.offices.index') }}" class="btn-secondary">{{ __('ui.admin.back_offices') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.offices.update', $office) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.office_name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        @include('partials.catalog-ar-fields-office', ['office' => $office])

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.municipality') }}</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $office->municipality) }}">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.address') }}</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.contact_number') }}</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $office->contact_number) }}">
            @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.contact_email') }}</label>
            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $office->contact_email) }}">
            @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.working_hours') }}</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours') !== null ? format_working_hours_for_input(old('working_hours')) : format_working_hours_for_input($office->working_hours) }}" placeholder="{{ __('ui.placeholders.working_hours') }}">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.admin.update_office') }}</button>
            <a href="{{ route('admin.offices.index') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-form-page>
@endsection