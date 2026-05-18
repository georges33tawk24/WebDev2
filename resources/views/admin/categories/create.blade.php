@extends('layouts.admin')

@section('title', __('ui.admin.create_category'))
@section('page-title', __('ui.admin.create_category'))

@section('content')
@php($catalogPrefix = $catalogPrefix ?? 'admin')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.create_category') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.create_category_sub') }}</div>
    </div>
    <a href="{{ route($catalogPrefix . '.categories.index') }}" class="btn-secondary">{{ __('ui.admin.back_categories') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route($catalogPrefix . '.categories.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.category_name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('ui.placeholders.category_name') }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.name_ar') }}</label>
            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar') }}" dir="rtl">
            @error('name_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description') }}</label>
            <textarea name="description" class="form-control" rows="4" placeholder="{{ __('ui.placeholders.category_description') }}">{{ old('description') }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description_ar') }}</label>
            <textarea name="description_ar" class="form-control" rows="4" dir="rtl">{{ old('description_ar') }}</textarea>
            @error('description_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.admin.create_category_btn') }}</button>
            <a href="{{ route($catalogPrefix . '.categories.index') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-form-page>
@endsection