@extends('layouts.admin')

@section('title', __('ui.admin.create_office'))
@section('page-title', __('ui.admin.create_office'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.create_office') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.create_office_sub') }}</div>
    </div>
    <a href="{{ route('admin.offices.index') }}" class="btn-secondary">{{ __('ui.admin.back_offices') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.offices.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.office_name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('ui.placeholders.office_name') }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        @include('partials.catalog-ar-fields-office', ['office' => new \App\Models\Office])

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.municipality') }}</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality') }}" placeholder="{{ __('ui.placeholders.municipality') }}">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.address') }}</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="{{ __('ui.placeholders.address') }}">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.contact_number') }}</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}" placeholder="{{ __('ui.placeholders.contact_number') }}">
            @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.contact_email') }}</label>
            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email') }}" placeholder="{{ __('ui.placeholders.contact_email') }}">
            @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.working_hours') }}</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours') }}" placeholder="{{ __('ui.placeholders.working_hours') }}">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.admin.create_office_btn') }}</button>
            <a href="{{ route('admin.offices.index') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-form-page>
@endsection