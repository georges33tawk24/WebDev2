@extends('layouts.admin')

@section('title', __('ui.citizen.feedback_title'))
@section('page-title', __('ui.citizen.feedback_title'))

@section('content')
<x-form-page>
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        {{ __('ui.citizen.feedback_title') }}
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        {{ __('ui.citizen.feedback_rate_for') }}
        <strong>{{ $serviceRequest->service?->localized('name') ?? __('ui.citizen.service_removed') }}</strong>
    </p>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:14px; border-radius:10px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('citizen.feedback.store', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                {{ __('ui.citizen.rating') }}
            </label>

            <select name="rating"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
                <option value="">{{ __('ui.citizen.choose_rating') }}</option>
                <option value="5">{{ __('ui.citizen.rating_5') }}</option>
                <option value="4">{{ __('ui.citizen.rating_4') }}</option>
                <option value="3">{{ __('ui.citizen.rating_3') }}</option>
                <option value="2">{{ __('ui.citizen.rating_2') }}</option>
                <option value="1">{{ __('ui.citizen.rating_1') }}</option>
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                {{ __('ui.citizen.comment') }}
            </label>

            <textarea name="comment"
                      rows="5"
                      placeholder="{{ __('ui.citizen.feedback_placeholder') }}"
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('comment') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('citizen.history') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                {{ __('ui.back') }}
            </a>

            <button type="submit" class="btn-primary">
                {{ __('ui.citizen.submit_feedback') }}
            </button>
        </div>
    </form>
</div>
</x-form-page>
@endsection
