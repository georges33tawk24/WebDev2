@extends('layouts.admin')

@section('title', __('ui.admin.edit_category'))
@section('page-title', __('ui.admin.edit_category'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.edit_category') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.edit_category_sub', ['name' => $category->localized('name')]) }}</div>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary"> {{ __('ui.admin.back_categories') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.category_name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.name_ar') }}</label>
            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $category->name_ar) }}" dir="rtl">
            @error('name_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description') }}</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description_ar') }}</label>
            <textarea name="description_ar" class="form-control" rows="4" dir="rtl">{{ old('description_ar', $category->description_ar) }}</textarea>
            @error('description_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.admin.update_category') }}</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-form-page>
@endsection