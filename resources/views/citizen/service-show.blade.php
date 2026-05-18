@extends('layouts.admin')

@section('title', 'Service Details')
@section('page-title', 'Service Details')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px;">
        <div>
            <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
                {{ $service->name }}
            </h1>

            <p style="color:#6b7280; margin-bottom:18px;">
                {{ $service->office->name ?? 'No office assigned' }}
                @if($service->category)
                    • {{ $service->category->name }}
                @endif
            </p>
        </div>

        <a href="{{ route('citizen.requests.create', $service) }}"
           class="btn-primary"
           style="text-decoration:none;">
            Apply Now
        </a>
    </div>

    <div style="margin-top:24px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">Description</h2>
        <p style="color:#374151; line-height:1.7;">
            {{ $service->description ?? 'No description available.' }}
        </p>
    </div>

    <div class="stat-grid" style="margin-top:28px;">
        <div class="stat-card">
            <span class="stat-label">Price</span>
            <span class="stat-number">${{ number_format($service->price, 2) }}</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Estimated Duration</span>
            <span class="stat-number" style="font-size:24px;">
                {{ $service->estimated_duration_minutes ?? 'N/A' }}
                @if($service->estimated_duration_minutes)
                    min
                @endif
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Office</span>
            <span class="stat-number" style="font-size:20px;">
                {{ $service->office->name ?? 'N/A' }}
            </span>
        </div>
    </div>

    <div style="margin-top:28px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">Required Documents</h2>

        @if(!empty($service->required_documents))
            <ul style="padding-left:20px; color:#374151; line-height:1.8;">
                @foreach($service->required_documents as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
        @else
            <p style="color:#6b7280;">No required documents listed.</p>
        @endif
    </div>

    <div style="margin-top:32px; display:flex; gap:12px;">
        <a href="{{ route('citizen.services') }}"
           class="btn-secondary"
           style="text-decoration:none;">
            Back to Services
        </a>

        <a href="{{ route('citizen.requests.create', $service) }}"
           class="btn-primary"
           style="text-decoration:none;">
            Submit Request
        </a>
    </div>
</div>
<div class="card" style="margin-top:24px;">
    <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
        Citizen Reviews
    </h2>

    @if($averageRating)
        <p style="color:#6b7280; margin-bottom:20px;">
            Average Rating:
            <strong>{{ $averageRating }}/5</strong>
        </p>
    @else
        <p style="color:#6b7280; margin-bottom:20px;">
            No ratings yet.
        </p>
    @endif

    @forelse($feedbacks as $feedback)
        <div style="border:1px solid #e5e7eb; border-radius:12px; padding:16px; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <div>
                    <p style="font-weight:700; margin-bottom:4px;">
                        {{ $feedback->citizen->name ?? 'Citizen' }}
                    </p>

                    <p style="color:#f59e0b; margin-bottom:8px;">
                        {{ str_repeat('★', $feedback->rating) }}
                        {{ str_repeat('☆', 5 - $feedback->rating) }}
                    </p>
                </div>

                <p style="color:#6b7280; font-size:14px;">
                    {{ optional($feedback->created_at)->format('d M Y') }}
                </p>
            </div>

            @if($feedback->comment)
                <p style="color:#374151; line-height:1.6;">
                    {{ $feedback->comment }}
                </p>
            @endif

            @if($feedback->public_reply)
                <div style="background:#f9fafb; border-left:4px solid #2563eb; padding:12px; margin-top:12px;">
                    <p style="font-weight:700; margin-bottom:4px;">Office Reply</p>
                    <p style="color:#374151;">{{ $feedback->public_reply }}</p>
                </div>
            @endif
        </div>
    @empty
        <p style="color:#6b7280;">
            No citizen reviews for this service yet.
        </p>
    @endforelse
</div>
@endsection