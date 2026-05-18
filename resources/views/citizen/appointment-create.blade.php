@extends('layouts.admin')

@section('title', __('ui.citizen.confirm_appointment'))
@section('page-title', __('ui.citizen.confirm_appointment'))

@section('content')
<x-form-page>
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">{{ __('ui.citizen.confirm_appointment') }}</h1>
    <p style="color:#6b7280; margin-bottom:24px;">{{ __('ui.citizen.office_colon') }} <strong>{{ $office->localized('name') }}</strong></p>

    <form method="POST" action="{{ route('citizen.appointments.store') }}">
        @csrf

        <input type="hidden" name="office_id" value="{{ $office->id }}">

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">{{ __('ui.citizen.appointment_date') }}</label>
            <input type="date"
                   name="appointment_date"
                   required
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">{{ __('ui.citizen.available_time_slot') }}</label>
            <select name="appointment_time"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
                <option value="">{{ __('ui.citizen.select_time') }}</option>
                @foreach (appointment_time_slots() as $slot)
                    <option value="{{ $slot }}" @selected(old('appointment_time') === $slot)>{{ localized_time_option($slot) }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">{{ __('ui.citizen.reason_notes') }}</label>
            <textarea name="notes"
                      rows="4"
                      placeholder="{{ __('ui.citizen.additional_notes') }}"
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;"></textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('citizen.appointments') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                {{ __('ui.back') }}
            </a>

            <button type="submit" class="btn-primary">
                {{ __('ui.citizen.confirm_booking') }}
            </button>
        </div>
    </form>
</div>
</x-form-page>
@endsection