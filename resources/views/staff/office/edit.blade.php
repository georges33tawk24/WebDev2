@extends('layouts.admin')

@section('title', __('ui.staff.office_profile'))
@section('page-title', __('ui.staff.office_profile'))

@section('content')
<x-form-page>
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.office_profile') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.office_profile_sub_manage') }}</div>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('staff.office.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Office Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        @include('partials.catalog-ar-fields-office', ['office' => $office])

        <div class="form-group">
            <label class="form-label">{{ __('ui.table.municipality') }}</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $office->municipality) }}" placeholder="{{ __('ui.placeholders.municipality') }}">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.address') }}</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}" placeholder="{{ __('ui.placeholders.address') }}">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">{{ __('ui.admin.contact_number') }}</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $office->contact_number) }}" placeholder="{{ __('ui.placeholders.contact_number') }}">
                @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('ui.admin.contact_email') }}</label>
                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $office->contact_email) }}" placeholder="{{ __('ui.placeholders.contact_email') }}">
                @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('ui.admin.working_hours') }}</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours') !== null ? format_working_hours_for_input(old('working_hours')) : format_working_hours_for_input($office->working_hours) }}" placeholder="{{ __('ui.placeholders.working_hours') }}">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Google Maps --}}
        <div class="form-group">
            <label class="form-label">{{ __('ui.staff.pin_office_map') }}</label>
            <p style="font-size:12px; color:#6b7280; margin-bottom:8px;">{{ __('ui.staff.click_map_hint') }}</p>
            <div id="map" style="width:100%; height:350px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:12px;"></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Latitude</label>
                <input type="number" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $office->latitude) }}" step="any" placeholder="{{ __('ui.placeholders.latitude') }}">
                @error('latitude') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Longitude</label>
                <input type="number" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $office->longitude) }}" step="any" placeholder="{{ __('ui.placeholders.longitude') }}">
                @error('longitude') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('ui.staff.update_office_profile') }}</button>
            <a href="{{ route('dashboard.staff') }}" class="btn-secondary">{{ __('ui.cancel') }}</a>
        </div>
    </form>
</div>

<script>
    let map;
    let marker;

    function initMap() {
        const defaultLocation = {
            lat: {{ $office->latitude ?? 33.8938 }},
            lng: {{ $office->longitude ?? 35.5018 }}
        };

        map = new google.maps.Map(document.getElementById('map'), {
            center: defaultLocation,
            zoom: 14,
        });

        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true,
            title: '{{ $office->localized('name') }}'
        });

        // Update lat/lng fields when marker is dragged
        marker.addListener('dragend', function() {
            document.getElementById('latitude').value = marker.getPosition().lat();
            document.getElementById('longitude').value = marker.getPosition().lng();
        });

        // Update marker position when map is clicked
        map.addListener('click', function(event) {
            marker.setPosition(event.latLng);
            document.getElementById('latitude').value = event.latLng.lat();
            document.getElementById('longitude').value = event.latLng.lng();
        });
    }
</script>

@if (filled(config('services.google.maps_key')))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap" async defer></script>
@else
<p class="form-error" style="margin-top:8px;">{{ __('ui.staff.google_maps_not_configured') }}</p>
@endif

</x-form-page>
@endsection