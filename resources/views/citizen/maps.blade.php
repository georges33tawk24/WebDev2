@extends('layouts.admin')

@section('title', 'Government Offices Map')
@section('page-title', 'Government Offices Map')

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">Government Offices</h1>
    <p style="color:#6b7280; margin-bottom:24px;">View available offices and their services.</p>

    <input type="text"
           id="officeSearch"
           placeholder="Search offices..."
           onkeyup="filterOffices()"
           style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-bottom:20px;">

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px;">
        @forelse($offices as $office)
            <div class="office-card" style="border:1px solid #e5e7eb; border-radius:14px; padding:20px;">
                <h2 style="font-size:20px; font-weight:700;">{{ $office->name }}</h2>
                <p style="color:#6b7280;">{{ $office->address ?? 'No address available' }}</p>
                <p style="color:#6b7280;">{{ $office->phone ?? 'No phone available' }}</p>

                <a href="{{ route('citizen.appointments.create', $office) }}"
                   class="btn-primary"
                   style="display:inline-block; text-decoration:none; margin-top:16px;">
                    Book Appointment
                </a>
            </div>
        @empty
            <p style="color:#6b7280;">No offices found.</p>
        @endforelse
    </div>
</div>

<script>
function filterOffices() {
    let input = document.getElementById("officeSearch").value.toLowerCase();
    let cards = document.querySelectorAll(".office-card");

    cards.forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(input) ? "block" : "none";
    });
}
</script>
@endsection