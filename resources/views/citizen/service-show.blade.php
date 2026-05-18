@extends('layouts.admin')

@section('title', __('ui.citizen.service_details'))
@section('page-title', __('ui.citizen.service_details'))

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px;">
        <div>
            <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
                {{ $service->localized('name') }}
            </h1>

            <p style="color:#6b7280; margin-bottom:18px;">
                {{ $service->office?->localized('name') ?? __('ui.no_office_assigned') }}
                @if($service->category)
                    • {{ $service->category?->localized('name') }}
                @endif
            </p>
        </div>

        <a href="{{ route('citizen.requests.create', $service) }}"
           class="btn-primary"
           style="text-decoration:none;">
            {{ __('ui.citizen.apply_now') }}
        </a>
    </div>

    <div style="margin-top:24px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">{{ __('ui.admin.description') }}</h2>
        <p style="color:#374151; line-height:1.7;">
            {{ $service->localized('description') ?? __('ui.citizen.no_description') }}
        </p>
    </div>

    <div class="stat-grid" style="margin-top:28px;">
        <div class="stat-card">
            <span class="stat-label">{{ __('ui.table.price') }}</span>
            <span class="stat-number">{{ localized_money($service->price) }}</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">{{ __('ui.citizen.estimated_duration_label') }}</span>
            <span class="stat-number" style="font-size:24px;">
                @if($service->estimated_duration_minutes)
                    {{ localized_number($service->estimated_duration_minutes) }} {{ __('ui.citizen.min_abbr') }}
                @else
                    {{ __('ui.na') }}
                @endif
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">{{ __('ui.table.office') }}</span>
            <span class="stat-number" style="font-size:20px;">
                {{ $service->office?->localized('name') ?? __('ui.na') }}
            </span>
        </div>
    </div>

    <div style="margin-top:28px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">{{ __('ui.admin.required_documents') }}</h2>

        @php $requiredDocs = $service->localizedList('required_documents'); @endphp
        @if(!empty($requiredDocs))
            <ul style="padding-left:20px; color:#374151; line-height:1.8;">
                @foreach($requiredDocs as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
        @else
            <p style="color:#6b7280;">{{ __('ui.citizen.no_required_docs_listed') }}</p>
        @endif
    </div>

    <div style="margin-top:32px; display:flex; gap:12px;">
        <a href="{{ route('citizen.services') }}"
           class="btn-secondary"
           style="text-decoration:none;">
            {{ __('ui.citizen.back_services') }}
        </a>

        <a href="{{ route('citizen.requests.create', $service) }}"
           class="btn-primary"
           style="text-decoration:none;">
            {{ __('ui.citizen.submit_request') }}
        </a>
    </div>
</div>
<div class="card" style="margin-top:24px;">
    <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
        {{ __('ui.citizen.reviews_title') }}
    </h2>

    @if($averageRating)
        <p style="color:#6b7280; margin-bottom:20px;">
            {{ __('ui.citizen.average_rating') }}
            <strong>{{ localized_number($averageRating) }}/{{ localized_digits('5') }}</strong>
        </p>
    @else
        <p style="color:#6b7280; margin-bottom:20px;">
            {{ __('ui.citizen.no_ratings') }}
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
                    {{ $feedback->created_at ? localized_date($feedback->created_at) : __('ui.na') }}
                </p>
            </div>

            @if($feedback->comment)
                <p style="color:#374151; line-height:1.6;">
                    {{ $feedback->comment }}
                </p>
            @endif

            @if($feedback->public_reply)
                <div style="background:#f9fafb; border-left:4px solid #2563eb; padding:12px; margin-top:12px;">
                    <p style="font-weight:700; margin-bottom:4px;">{{ __('ui.citizen.office_reply') }}</p>
                    <p style="color:#374151;">{{ $feedback->public_reply }}</p>
                </div>
            @endif
        </div>
    @empty
        <p style="color:#6b7280;">
            {{ __('ui.citizen.no_reviews_yet') }}
        </p>
    @endforelse
</div>
@endsection