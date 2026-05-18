@extends('layouts.admin')

@section('title', 'Government Offices Map')
@section('page-title', 'Government Offices Map')

@section('content')

<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        Government Offices Map
    </h1>

    <p style="color:#6b7280; margin-bottom:20px;">
        Find nearby government offices and explore their services.
    </p>

    <div style="display:flex; gap:12px; margin-bottom:20px;">

        <input type="text"
               id="officeSearch"
               placeholder="Search offices..."
               style="flex:1; border:1px solid #d1d5db; border-radius:10px; padding:12px;">

        <button type="button"
                onclick="findNearestOffice()"
                class="btn-primary">
            Find Nearest Office
        </button>

    </div>

    <div id="map"
         style="width:100%; height:550px; border-radius:14px; border:1px solid #e5e7eb;">
    </div>
</div>

<script>
    const offices = @json($offices);

    let map;
    let markers = [];
    let userMarker = null;

    function initMap() {

        const defaultCenter = {
            lat: 33.8938,
            lng: 35.5018
        };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 11,
            center: defaultCenter,
        });

        offices.forEach(office => {

            const position = {
                lat: parseFloat(office.latitude),
                lng: parseFloat(office.longitude)
            };

            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: office.name
            });

            const servicesUrl =
                "{{ route('citizen.services') }}" +
                "?office_id=" + office.id;

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="max-width:240px;">

                        <h3 style="font-size:16px; font-weight:bold; margin-bottom:6px;">
                            ${office.name}
                        </h3>

                        <p style="margin-bottom:6px;">
                            <strong>Address:</strong>
                            ${office.address ?? 'No address available'}
                        </p>

                        <p style="margin-bottom:10px;">
                            <strong>Working Hours:</strong>
                            ${formatWorkingHours(office.working_hours)}
                        </p>

                        <a href="${servicesUrl}"
                           style="display:inline-block;
                                  background:#2563eb;
                                  color:white;
                                  padding:8px 12px;
                                  border-radius:8px;
                                  text-decoration:none;">
                            View Services
                        </a>

                    </div>
                `
            });

            marker.addListener("click", () => {
                infoWindow.open(map, marker);
            });

            markers.push({
                office: office,
                marker: marker,
                position: position,
                infoWindow: infoWindow
            });

        });

        document
            .getElementById("officeSearch")
            .addEventListener("keyup", filterOffices);
    }

    function formatWorkingHours(value) {

        if (!value) {
            return 'Not available';
        }

        try {

            const parsed =
                typeof value === 'string'
                ? JSON.parse(value)
                : value;

            return Object.entries(parsed)
                .map(([day, hours]) => `${day}: ${hours}`)
                .join('<br>');

        } catch (e) {

            return value;
        }
    }

    function filterOffices() {

        const search =
            document.getElementById("officeSearch")
            .value
            .toLowerCase();

        markers.forEach(item => {

            const match =
                item.office.name.toLowerCase().includes(search)
                ||
                (item.office.address ?? '')
                    .toLowerCase()
                    .includes(search);

            item.marker.setVisible(match);
        });
    }

    function findNearestOffice() {

        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(position => {

            const userLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            if (userMarker) {
                userMarker.setMap(null);
            }

            userMarker = new google.maps.Marker({
                position: userLocation,
                map: map,
                title: "Your Location",
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            });

            let nearest = null;
            let shortestDistance = Infinity;

            markers.forEach(item => {

                const distance = getDistance(
                    userLocation.lat,
                    userLocation.lng,
                    item.position.lat,
                    item.position.lng
                );

                if (distance < shortestDistance) {
                    shortestDistance = distance;
                    nearest = item;
                }
            });

            if (nearest) {

                map.setCenter(nearest.position);
                map.setZoom(14);

                nearest.infoWindow.open(map, nearest.marker);
            }

        }, () => {

            alert("Location permission denied.");

        });
    }

    function getDistance(lat1, lon1, lat2, lon2) {

        const R = 6371;

        const dLat = degToRad(lat2 - lat1);
        const dLon = degToRad(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(degToRad(lat1)) *
            Math.cos(degToRad(lat2)) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);

        const c = 2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

        return R * c;
    }

    function degToRad(deg) {
        return deg * (Math.PI / 180);
    }
</script>

@if (filled($googleMapsApiKey))
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initMap">
    </script>
@else
    <p class="field-hint-block" style="margin-top:12px; color:#b45309;">
        Google Maps is not configured. Add <code>GOOGLE_MAPS_API_KEY</code> to your <code>.env</code> file.
    </p>
@endif

@endsection