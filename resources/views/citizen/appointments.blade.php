@extends('layouts.admin')

@section('title', __('ui.citizen.appointments_title'))
@section('page-title', __('ui.citizen.appointments_title'))

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">{{ __('ui.citizen.appointments_title') }}</h1>
    <p style="color:#6b7280; margin-bottom:24px;">{{ __('ui.citizen.choose_office_appointment') }}</p>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:14px; border-radius:10px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px;">
        @forelse($offices as $office)
            <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px;">
                <h2 style="font-size:20px; font-weight:700;">{{ $office->localized('name') }}</h2>
                <p style="color:#6b7280;">{{ $office->address ?? __('ui.citizen.no_address') }}</p>

                <a href="{{ route('citizen.appointments.create', $office) }}"
                   class="btn-primary"
                   style="display:inline-block; text-decoration:none; margin-top:16px;">
                    {{ __('ui.citizen.select_office_btn') }}
                </a>
            </div>
        @empty
            <p style="color:#6b7280;">{{ __('ui.citizen.no_offices_available') }}</p>
        @endforelse
    </div>
</div>
@endsection