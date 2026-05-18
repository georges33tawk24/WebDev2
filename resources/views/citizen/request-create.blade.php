@extends('layouts.admin')

@section('title', __('ui.citizen.submit_request'))
@section('page-title', __('ui.citizen.submit_request'))

@section('content')
<x-form-page class="form-page--wide">
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        {{ __('ui.citizen.submit_service_request') }}
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        {{ __('ui.citizen.applying_for') }} <strong>{{ $service->localized('name') }}</strong>
    </p>

    <div class="card" style="background:#f9fafb; margin-bottom:24px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">{{ __('ui.citizen.service_summary') }}</h2>

        <p><strong>{{ __('ui.citizen.office_colon') }}</strong> {{ $service->office?->localized('name') ?? __('ui.na') }}</p>
        <p><strong>{{ __('ui.table.category') }}:</strong> {{ $service->category?->localized('name') ?? __('ui.na') }}</p>
        <p><strong>{{ __('ui.table.price') }}:</strong> {{ localized_money($service->price) }}</p>
        <p>
            <strong>{{ __('ui.citizen.duration') }}:</strong>
            {{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' ' . __('ui.citizen.minutes') : __('ui.na') }}
        </p>
    </div>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:14px; border-radius:10px; margin-bottom:20px;">
            <strong>{{ __('ui.citizen.fix_errors') }}</strong>
            <ul style="margin-top:8px; padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('citizen.requests.store') }}"
          enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="service_id" value="{{ $service->id }}">

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                {{ __('ui.citizen.additional_info') }}
            </label>

            <textarea name="notes"
                      rows="5"
                      placeholder="{{ __('ui.citizen.notes_placeholder') }}"
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('notes') }}</textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                {{ __('ui.citizen.upload_required_docs') }}
            </label>

            <input type="file"
                   name="documents[]"
                   multiple
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; background:white;">

            <p style="color:#6b7280; font-size:14px; margin-top:8px;">
                {{ __('ui.citizen.allowed_files_hint') }}
            </p>
        </div>

        <div class="form-actions">
            <a href="{{ route('citizen.services.show', $service) }}"
               class="btn-secondary"
               style="text-decoration:none;">
                {{ __('ui.back') }}
            </a>

            <button type="submit" class="btn-primary">
                {{ __('ui.citizen.submit_request') }}
            </button>
        </div>
    </form>
</div>
</x-form-page>
@endsection