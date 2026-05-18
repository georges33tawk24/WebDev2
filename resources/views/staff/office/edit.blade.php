@extends('layouts.staff')

@section('title', 'Office Profile')
@section('page-title', 'Office Profile')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Office Profile</div>
        <div class="page-subtitle">Manage your office details and contact information</div>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('staff.office.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Office Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Municipality</label>
            <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $office->municipality) }}" placeholder="e.g. Beirut">
            @error('municipality') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}" placeholder="e.g. Downtown Beirut, Main Street">
            @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $office->contact_number) }}" placeholder="e.g. +961 1 234 567">
                @error('contact_number') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $office->contact_email) }}" placeholder="e.g. office@municipality.gov.lb">
                @error('contact_email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Working Hours</label>
            <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours', is_array($office->working_hours) ? implode(', ', $office->working_hours) : $office->working_hours) }}" placeholder="e.g. Mon-Fri 8:00AM - 4:00PM">
            @error('working_hours') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Google Maps --}}
        <div class="form-group">
            <label class="form-label">Pin Office Location on Map</label>
            <p style="font-size:12px; color:#6b7280; margin-bottom:8px;">Click on the map to set your office location</p>
            <div id="map" style="width:100%; height:350px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:12px;"></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Latitude</label>
                <input type="number" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $office->latitude) }}" step="any" placeholder="e.g. 33.8938">
                @error('latitude') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Longitude</label>
                <input type="number" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $office->longitude) }}" step="any" placeholder="e.g. 35.5018">
                @error('longitude') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn-primary">Update Office Profile</button>
            <a href="{{ route('dashboard.staff') }}" class="btn-secondary">Cancel</a>
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
            title: '{{ $office->name }}'
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
<p class="form-error" style="margin-top:8px;">Google Maps is not configured. Add GOOGLE_MAPS_API_KEY to .env.</p>
@endif

@endsection