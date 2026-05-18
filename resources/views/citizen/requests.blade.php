@extends('layouts.admin')

@section('title', __('ui.citizen.requests_title'))
@section('page-title', __('ui.citizen.requests_title'))

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">{{ __('ui.citizen.requests_title') }}</h1>
            <p style="color:#6b7280;">{{ __('ui.citizen.requests_sub') }}</p>
        </div>

        <a href="{{ route('citizen.services') }}"
           class="btn-primary"
           style="text-decoration:none;">
            {{ __('ui.citizen.new_request') }}
        </a>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:14px; border-radius:10px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($requests as $request)
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

                        @if($history->comment)
                            <p style="font-size:14px; color:#374151;">
                                {{ $history->comment }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p style="color:#6b7280;">{{ __('ui.citizen.no_status_history') }}</p>
                @endforelse
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            {{ __('ui.citizen.no_requests') }}
        </div>
    @endforelse

    <div style="margin-top:24px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection
