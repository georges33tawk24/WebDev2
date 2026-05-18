@extends('layouts.admin')

@section('title', __('ui.citizen.requests_title'))
@section('page-title', __('ui.citizen.requests_title'))

@section('content')

<div class="card">

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:28px;
        gap:20px;
        flex-wrap:wrap;
    ">
        <div>
            <h1 style="
                font-size:30px;
                font-weight:700;
                margin-bottom:8px;
            ">
                My Service Requests
            </h1>

            <p style="
                color:#6b7280;
                line-height:1.7;
                max-width:700px;
            ">
                Monitor all submitted requests, track realtime status updates,
                access QR tracking, communicate with office staff,
                and review payment activity from one unified dashboard.
            </p>
            <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">{{ __('ui.citizen.requests_title') }}</h1>
            <p style="color:#6b7280;">{{ __('ui.citizen.requests_sub') }}</p>
        </div>

        <a href="{{ route('citizen.services') }}"
           class="btn-primary"
           style="text-decoration:none;">
            + Submit New Request
            {{ __('ui.citizen.new_request') }}
        </a>
    </div>

    @if(session('success'))
        <div style="
            background:#dcfce7;
            color:#166534;
            padding:16px;
            border-radius:12px;
            margin-bottom:24px;
            border:1px solid #bbf7d0;
        ">
            {{ session('success') }}
        </div>
    @endif

    @forelse($requests as $request)

        @php

            $statusColors = [
                'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                'in_review' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
                'missing_documents' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                'approved' => ['bg' => '#dcfce7', 'text' => '#166534'],
                'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                'completed' => ['bg' => '#ede9fe', 'text' => '#6d28d9'],
            ];

            $currentStatus = $statusColors[$request->status] ?? [
                'bg' => '#f3f4f6',
                'text' => '#374151'
            ];

        @endphp

        <div style="
            border:1px solid #e5e7eb;
            border-radius:20px;
            padding:24px;
            margin-bottom:24px;
            background:white;
            transition:0.2s;
        ">

            <div style="
                display:flex;
                justify-content:space-between;
                gap:24px;
                flex-wrap:wrap;
                align-items:flex-start;
            ">

                <div style="flex:1; min-width:280px;">

                    <div style="
                        display:flex;
                        align-items:center;
                        gap:12px;
                        margin-bottom:10px;
                        flex-wrap:wrap;
                    ">

                        <h2 style="
                            font-size:22px;
                            font-weight:700;
                            margin:0;
                        ">
                            {{ $request->service->name ?? 'Service removed' }}
                        </h2>

                        <span style="
                            background:{{ $currentStatus['bg'] }};
                            color:{{ $currentStatus['text'] }};
                            padding:8px 14px;
                            border-radius:999px;
                            font-size:13px;
                            font-weight:600;
                        ">
                            {{ ucwords(str_replace('_', ' ', $request->status)) }}
                        </span>

                    </div>

                    <div style="
                        display:grid;
                        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                        gap:16px;
                        margin-top:22px;
                    ">

                        <div>
                            <div style="font-size:13px; color:#6b7280;">
                                Reference Number
                            </div>

                            <div style="font-weight:600;">
                                {{ $request->reference_number }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:13px; color:#6b7280;">
                                Government Office
                            </div>

                            <div style="font-weight:600;">
                                {{ $request->office->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:13px; color:#6b7280;">
                                Submitted Date
                            </div>

                            <div style="font-weight:600;">
                                {{ optional($request->created_at)->format('d M Y - h:i A') }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:13px; color:#6b7280;">
                                Payment Status
                            </div>

                            @if($request->payments->where('status', 'paid')->count() > 0)

                                <div style="
                                    display:inline-block;
                                    margin-top:6px;
                                    background:#dcfce7;
                                    color:#166534;
                                    padding:7px 12px;
                                    border-radius:999px;
                                    font-size:13px;
                                    font-weight:600;
                                ">
                                    Paid
                                </div>

                            @else

                                <div style="
                                    display:inline-block;
                                    margin-top:6px;
                                    background:#fee2e2;
                                    color:#991b1b;
                                    padding:7px 12px;
                                    border-radius:999px;
                                    font-size:13px;
                                    font-weight:600;
                                ">
                                    Unpaid
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

                <div style="
                    display:flex;
                    flex-direction:column;
                    gap:12px;
                    min-width:220px;
                ">

                    <a href="{{ route('citizen.requests.qr', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; justify-content:center;">
                        View QR Tracking
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; gap:20px; align-items:flex-start;">
                <div>
                    <h2 style="font-size:20px; font-weight:700; margin-bottom:6px;">
                        {{ $request->service?->localized('name') ?? __('ui.citizen.service_removed') }}
                    </h2>

                    <p style="color:#6b7280; font-size:14px;">
                        {{ __('ui.citizen.reference_colon') }} <strong>{{ $request->reference_number }}</strong>
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        {{ __('ui.citizen.office_colon') }} {{ $request->office?->localized('name') ?? __('ui.na') }}
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        {{ __('ui.citizen.submitted_colon') }} {{ $request->created_at ? localized_datetime($request->created_at) : __('ui.na') }}
                    </p>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                    <span style="padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600; background:#fef3c7; color:#92400e;">
                        {{ __('ui.status.'.$request->status) }}
                    </span>

                    @if($request->payments->where('status', 'paid')->count() > 0)
                        <span style="background:#dcfce7; color:#166534; padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                            {{ __('ui.citizen.paid') }}
                        </span>
                    @else
                        <span style="background:#fee2e2; color:#991b1b; padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                            {{ __('ui.citizen.unpaid') }}
                        </span>
                    @endif

                    <a href="{{ route('citizen.requests.qr', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; text-align:center; margin-top:8px;">
                        {{ __('ui.citizen.qr_code') }}
                    </a>

                    <a href="{{ route('citizen.chat', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; justify-content:center;">
                        Open Chat
                    </a>

                    @if($request->payments->where('status', 'paid')->count() === 0)

                        <a href="{{ route('citizen.payments') }}"
                           class="btn-primary"
                           style="text-decoration:none; justify-content:center;">
                            Continue Payment
                        </a>

                    @endif

                </div>

            </div>

            <div style="
                margin-top:28px;
                border-top:1px solid #e5e7eb;
                padding-top:22px;
            ">

                <h3 style="
                    font-size:17px;
                    font-weight:700;
                    margin-bottom:18px;
                ">
                    Request Timeline
                </h3>

                @forelse($request->statusHistories->sortByDesc('changed_at') as $history)

                    <div style="
                        display:flex;
                        gap:16px;
                        margin-bottom:18px;
                    ">

                        <div style="
                            width:14px;
                            min-width:14px;
                            height:14px;
                            border-radius:50%;
                            background:#2563eb;
                            margin-top:6px;
                        "></div>

                        <div>

                            <div style="
                                font-weight:700;
                                margin-bottom:4px;
                            ">
                                {{ ucwords(str_replace('_', ' ', $history->to_status)) }}
                            </div>

                            <div style="
                                font-size:13px;
                                color:#6b7280;
                                margin-bottom:6px;
                            ">
                                {{ optional($history->changed_at)->format('d M Y - h:i A') }}
                            </div>

                            @if($history->comment)

                                <div style="
                                    font-size:14px;
                                    color:#374151;
                                    line-height:1.7;
                                ">
                                    {{ $history->comment }}
                                </div>

                            @endif

                        </div>
                       style="text-decoration:none; text-align:center;">
                        {{ __('ui.citizen.chat') }}
                    </a>
                </div>
            </div>

            <div style="margin-top:20px;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:10px;">{{ __('ui.staff.status_history') }}</h3>

                @forelse($request->statusHistories as $history)
                    <div style="border-left:3px solid #2563eb; padding-left:12px; margin-bottom:12px;">
                        <p style="font-weight:600;">
                            {{ __('ui.status.'.$history->to_status) }}
                        </p>

                        <p style="font-size:14px; color:#6b7280;">
                            {{ $history->changed_at ? localized_datetime($history->changed_at) : __('ui.na') }}
                        </p>

                    </div>

                @empty

                    <p style="color:#6b7280;">
                        No status history available yet.
                    </p>

                    <p style="color:#6b7280;">{{ __('ui.citizen.no_status_history') }}</p>
                @endforelse

            </div>

        </div>

    @empty

        <div style="
            text-align:center;
            padding:70px 20px;
            color:#6b7280;
        ">

            <h2 style="
                font-size:24px;
                margin-bottom:10px;
            ">
                No Requests Yet
            </h2>

            <p style="
                margin-bottom:24px;
                line-height:1.7;
            ">
                You have not submitted any government service requests yet.
            </p>

            <a href="{{ route('citizen.services') }}"
               class="btn-primary"
               style="text-decoration:none;">
                Browse Services
            </a>

        <div style="text-align:center; padding:40px; color:#6b7280;">
            {{ __('ui.citizen.no_requests') }}
        </div>

    @endforelse

    <div style="margin-top:28px;">
        {{ $requests->links() }}
    </div>

</div>

@endsection
@endsection
