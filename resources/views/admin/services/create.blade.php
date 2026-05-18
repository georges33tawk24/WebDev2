@extends('layouts.admin')

@section('title', __('ui.admin.create_service'))
@section('page-title', __('ui.admin.create_service'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.admin.create_service') }}</div>
        <div class="page-subtitle">{{ __('ui.admin.create_service_sub') }}</div>
    </div>
    <a href="{{ route('admin.services.index') }}" class="btn-secondary">{{ __('ui.admin.back_services') }}</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.office') }}</label>
            <select name="office_id" class="form-control">
                <option value="">{{ __('ui.admin.select_an_office') }}...</option>
                @foreach($offices as $office)
                    <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                        {{ $office->localized('name') }}
                    </option>
                @endforeach
            </select>
            @error('office_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.category') }}</label>
            <select name="category_id" class="form-control">
                <option value="">{{ __('ui.admin.select_a_category') }}...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->localized('name') }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.service_name') }} </label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('ui.placeholders.service_name') }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.name_ar') }}</label>
            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar') }}" dir="rtl">
            @error('name_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description') }}</label>
            <textarea name="description" class="form-control" rows="4" placeholder="{{ __('ui.placeholders.service_description') }}">{{ old('description') }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">{{ __('ui.admin.price_usd') }} </label>
                <input type="number" name="price" class="form-control" value="{{ old('price', 0) }}" min="0" step="0.01">
                @error('price') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('ui.admin.estimated_duration') }}</label>
                <input type="number" name="estimated_duration_minutes" class="form-control" value="{{ old('estimated_duration_minutes') }}" min="1" placeholder="{{ __('ui.placeholders.duration_minutes') }}">
                @error('estimated_duration_minutes') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.required_documents') }}</label>
            <input type="text" name="required_documents" class="form-control" value="{{ old('required_documents') }}" placeholder="{{ __('ui.placeholders.required_documents') }}">
            <small style="color:#6b7280; font-size:12px;">{{ __('ui.admin.required_docs_comma_hint') }}</small>
            @error('required_documents') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.description_ar') }}</label>
            <textarea name="description_ar" class="form-control" rows="4" dir="rtl">{{ old('description_ar') }}</textarea>
            @error('description_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.required_documents_ar') }}</label>
            <input type="text" name="required_documents_ar" class="form-control" value="{{ old('required_documents_ar') }}" placeholder="{{ __('ui.placeholders.required_documents') }}">
            <small style="color:#6b7280; font-size:12px;">{{ __('ui.admin.required_docs_comma_hint') }}</small>
            @error('required_documents_ar') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="form-label" style="margin:0;">{{ __('ui.active') }}</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.admin.create_service_btn') }}</button>
            <a href="{{ route('admin.services.index') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>
</x-form-page>
@endsection