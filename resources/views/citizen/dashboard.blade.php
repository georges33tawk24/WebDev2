@extends('layouts.admin')

@section('title', __('ui.citizen.portal'))
@section('page-title', __('ui.citizen.portal'))

@section('content')

<div style="display:flex; flex-direction:column; gap:24px;">

    <div class="dashboard-intro">
        <h1 class="dashboard-intro__title">
            {{ __('ui.citizen.dashboard_title') }}
        </h1>

        <p class="dashboard-intro__subtitle">
            {!! __('ui.citizen.welcome', ['name' => '<bdi>'.e(auth()->user()->name).'</bdi>']) !!}
        </p>
    </div>

    <div class="stat-grid">

        <div class="stat-card">
            <span class="stat-label">{{ __('ui.citizen.total_requests') }}</span>
            <span class="stat-number">{{ localized_number($totalRequests) }}</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">{{ __('ui.citizen.pending_requests') }}</span>
            <span class="stat-number" style="color:#d97706;">
                {{ localized_number($pendingRequests) }}
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">{{ __('ui.citizen.completed_requests') }}</span>
            <span class="stat-number" style="color:#16a34a;">
                {{ localized_number($completedRequests) }}
            </span>
        </div>

    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px;">

        <a href="{{ route('citizen.services') }}"
           class="card"
           style="background:#2563eb; color:white; text-decoration:none;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                {{ __('ui.citizen.browse_services') }}
            </h2>

            <p style="opacity:0.9;">
                {{ __('ui.citizen.browse_services_sub') }}
            </p>
        </a>

        <a href="{{ route('citizen.requests') }}"
           class="card"
           style="text-decoration:none; color:inherit;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                {{ __('ui.citizen.track_requests') }}
            </h2>

            <p style="color:#6b7280;">
                {{ __('ui.citizen.track_requests_sub') }}
            </p>
        </a>

        <a href="{{ route('citizen.services') }}"
           class="card"
           style="text-decoration:none; color:inherit;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                {{ __('ui.citizen.quick_apply') }}
            </h2>

            <p style="color:#6b7280;">
                {{ __('ui.citizen.quick_apply_sub') }}
            </p>
        </a>

    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        <div class="card">
            <h2 style="font-size:24px; font-weight:700; margin-bottom:20px;">
                {{ __('ui.citizen.active_requests') }}
            </h2>

            @forelse($activeRequests as $request)
                <div style="padding:16px 0; border-bottom:1px solid #e5e7eb;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px;">
                        <div>
                            <p style="font-weight:700;">
                                {{ $request->service?->localized('name') ?? __('ui.citizen.service_removed') }}
                            </p>
                            <p style="color:#6b7280; font-size:14px;">
                                {{ __('ui.citizen.ref') }}: {{ $request->reference_number }}
                            </p>
                            <p style="color:#6b7280; font-size:14px;">
                                {{ $request->office?->localized('name') ?? 'N/A' }}
                            </p>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                            <span style="background:#fef3c7; color:#92400e; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                                {{ __('ui.status.'.$request->status) }}
                            </span>
                            @if($request->payments->where('status', 'paid')->count() > 0)
                                <span style="background:#dcfce7; color:#166534; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                                    {{ __('ui.citizen.paid') }}
                                </span>
                            @else
                                <span style="background:#fee2e2; color:#991b1b; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                                    {{ __('ui.citizen.unpaid') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:#6b7280;">{{ __('ui.citizen.no_active') }}</p>
            @endforelse
        </div>

        <div class="card">
            <h2 style="font-size:24px; font-weight:700; margin-bottom:20px;">
                {{ __('ui.citizen.recent_activity') }}
            </h2>

            @forelse($recentRequests as $request)
                <div style="padding:16px 0; border-bottom:1px solid #e5e7eb;">
                    <p style="font-weight:700;">
                        {{ $request->service?->localized('name') ?? __('ui.citizen.service_removed') }}
                    </p>
                    <p style="color:#6b7280; font-size:14px;">
                        {{ optional($request->created_at)->format('d M Y') }}
                    </p>
                    <p style="color:#6b7280; font-size:14px;">
                        {{ __('ui.citizen.status_label') }}: {{ __('ui.status.'.$request->status) }}
                    </p>
                </div>
            @empty
                <p style="color:#6b7280;">{{ __('ui.citizen.no_recent') }}</p>
            @endforelse
        </div>

    </div>

</div>

@endsection
