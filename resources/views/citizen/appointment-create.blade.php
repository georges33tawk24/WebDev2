@extends('layouts.admin')

@section('title', 'Confirm Appointment')
@section('page-title', 'Confirm Appointment')

@section('content')
<div class="card" style="max-width:700px; margin:auto;">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">Confirm Appointment</h1>
    <p style="color:#6b7280; margin-bottom:24px;">Office: <strong>{{ $office->name }}</strong></p>

    <form method="POST" action="{{ route('citizen.appointments.store') }}">
        @csrf

        <input type="hidden" name="office_id" value="{{ $office->id }}">

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">Appointment Date</label>
            <input type="date"
                   name="appointment_date"
                   required
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">Available Time Slot</label>
            <select name="appointment_time"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
                <option value="">Select time</option>
                <option value="09:00">09:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">01:00 PM</option>
                <option value="14:00">02:00 PM</option>
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">Reason / Notes</label>
            <textarea name="notes"
                      rows="4"
                      placeholder="Optional notes..."
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;"></textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <a href="{{ route('citizen.appointments') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                Back
            </a>

            <button type="submit" class="btn-primary">
                Confirm Booking
            </button>
        </div>
    </form>
</div>
@endsection