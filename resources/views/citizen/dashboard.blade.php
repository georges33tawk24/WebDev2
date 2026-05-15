@extends('layouts.admin')

@section('title', 'Citizen Portal')
@section('page-title', 'Citizen Portal')

@section('content')

<div style="display:flex; flex-direction:column; gap:24px;">

    <div>
        <h1 style="font-size:42px; font-weight:700; margin-bottom:10px;">
            Citizen Dashboard
        </h1>

        <p style="font-size:18px; color:#6b7280;">
            Welcome back, {{ auth()->user()->name }}. Manage your government service requests here.
        </p>
    </div>

    <div class="stat-grid">

        <div class="stat-card">
            <span class="stat-label">Total Requests</span>
            <span class="stat-number">{{ $totalRequests }}</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Pending Requests</span>
            <span class="stat-number" style="color:#d97706;">
                {{ $pendingRequests }}
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Completed Requests</span>
            <span class="stat-number" style="color:#16a34a;">
                {{ $completedRequests }}
            </span>
        </div>

    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px;">

        <a href="{{ route('citizen.services') }}"
           class="card"
           style="background:#2563eb; color:white; text-decoration:none;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                Browse Services
            </h2>

            <p style="opacity:0.9;">
                Find government services and submit requests.
            </p>
        </a>

        <a href="{{ route('citizen.requests') }}"
           class="card"
           style="text-decoration:none; color:inherit;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                Track Requests
            </h2>

            <p style="color:#6b7280;">
                Monitor submitted requests and statuses.
            </p>
        </a>

        <a href="{{ route('citizen.services') }}"
           class="card"
           style="text-decoration:none; color:inherit;">

            <h2 style="font-size:24px; font-weight:700; margin-bottom:10px;">
                Quick Apply
            </h2>

            <p style="color:#6b7280;">
                Start a new service request quickly.
            </p>
        </a>

    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        <div class="card">
            <h2 style="font-size:24px; font-weight:700; margin-bottom:20px;">
                Active Requests
            </h2>

            @forelse($activeRequests as $request)
                <div style="padding:16px 0; border-bottom:1px solid #e5e7eb;">

                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px;">

                        <div>
                            <p style="font-weight:700;">
                                {{ $request->service->name ?? 'Service removed' }}
                            </p>

                            <p style="color:#6b7280; font-size:14px;">
                                Ref: {{ $request->reference_number }}
                            </p>

                            <p style="color:#6b7280; font-size:14px;">
                                {{ $request->office->name ?? 'N/A' }}
                            </p>
                        </div>

                        <span style="background:#fef3c7; color:#92400e; padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                            {{ ucfirst($request->status) }}
                        </span>

                    </div>
                </div>
            @empty
                <p style="color:#6b7280;">
                    You have no active requests.
                </p>
            @endforelse
        </div>

        <div class="card">
            <h2 style="font-size:24px; font-weight:700; margin-bottom:20px;">
                Recent Activity
            </h2>

            @forelse($recentRequests as $request)
                <div style="padding:16px 0; border-bottom:1px solid #e5e7eb;">

                    <p style="font-weight:700;">
                        {{ $request->service->name ?? 'Service removed' }}
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        {{ optional($request->created_at)->format('d M Y') }}
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        Status: {{ ucfirst($request->status) }}
                    </p>

                </div>
            @empty
                <p style="color:#6b7280;">
                    No recent activity yet.
                </p>
            @endforelse
        </div>

    </div>

</div>

@endsection