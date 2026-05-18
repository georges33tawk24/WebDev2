@extends('layouts.admin')

@section('title', 'My Requests')
@section('page-title', 'My Requests')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">My Requests</h1>
            <p style="color:#6b7280;">Track your submitted service requests and status history.</p>
        </div>

        <a href="{{ route('citizen.services') }}"
           class="btn-primary"
           style="text-decoration:none;">
            New Request
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
                        {{ $request->service->name ?? 'Service removed' }}
                    </h2>

                    <p style="color:#6b7280; font-size:14px;">
                        Reference: <strong>{{ $request->reference_number }}</strong>
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        Office: {{ $request->office->name ?? 'N/A' }}
                    </p>

                    <p style="color:#6b7280; font-size:14px;">
                        Submitted: {{ optional($request->created_at)->format('d M Y - h:i A') }}
                    </p>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                    <span style="padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600; background:#fef3c7; color:#92400e;">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>

                    @if($request->payments->where('status', 'paid')->count() > 0)
                        <span style="background:#dcfce7; color:#166534; padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                            Paid
                        </span>
                    @else
                        <span style="background:#fee2e2; color:#991b1b; padding:7px 12px; border-radius:999px; font-size:13px; font-weight:600;">
                            Unpaid
                        </span>
                    @endif
                </div>
            </div>

            <div style="margin-top:20px;">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:10px;">Status History</h3>

                @forelse($request->statusHistories as $history)
                    <div style="border-left:3px solid #2563eb; padding-left:12px; margin-bottom:12px;">
                        <p style="font-weight:600;">
                            {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                        </p>

                        <p style="font-size:14px; color:#6b7280;">
                            {{ optional($history->changed_at)->format('d M Y - h:i A') }}
                        </p>

                        @if($history->comment)
                            <p style="font-size:14px; color:#374151;">
                                {{ $history->comment }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p style="color:#6b7280;">No status history yet.</p>
                @endforelse
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            You have not submitted any requests yet.
        </div>
    @endforelse

    <div style="margin-top:24px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection