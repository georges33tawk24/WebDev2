@extends('layouts.admin')

@section('title', 'History')
@section('page-title', 'History')

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">Request History</h1>
    <p style="color:#6b7280; margin-bottom:24px;">View your completed and previous service requests.</p>

    @forelse($requests as $request)
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; gap:20px;">
                <div>
                    <h2 style="font-size:20px; font-weight:700;">
                        {{ $request->service->name ?? 'Service removed' }}
                    </h2>

                    <p style="color:#6b7280;">Reference: {{ $request->reference_number }}</p>
                    <p style="color:#6b7280;">Office: {{ $request->office->name ?? 'N/A' }}</p>
                    <p style="color:#6b7280;">Submitted: {{ optional($request->created_at)->format('d M Y') }}</p>
                    <p style="color:#6b7280;">Status: {{ ucfirst(str_replace('_', ' ', $request->status)) }}</p>
                </div>

               <div style="display:flex; flex-direction:column; gap:10px;">

    <a href="{{ route('citizen.history.receipt', $request) }}"
       class="btn-secondary"
       style="text-decoration:none; text-align:center;">
        Download Receipt
    </a>

    <a href="{{ route('citizen.history.document', $request) }}"
       class="btn-secondary"
       style="text-decoration:none; text-align:center;">
        Download Document
    </a>

    <a href="{{ route('citizen.feedback.create', $request) }}"
       class="btn-secondary"
       style="text-decoration:none; text-align:center;">
        Leave Feedback
    </a>

</div>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            No request history available.
        </div>
    @endforelse
</div>
@endsection