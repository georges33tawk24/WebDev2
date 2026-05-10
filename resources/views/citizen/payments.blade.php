@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:28px; font-weight:700;">Payments</h1>
            <p style="color:#6b7280;">Manage and complete your service payments.</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:14px; border-radius:10px; margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($requests as $request)
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:20px; font-weight:700;">
                        {{ $request->service->name ?? 'Service removed' }}
                    </h2>

                    <p style="color:#6b7280;">
                        Ref: {{ $request->reference_number }}
                    </p>

                    <p style="color:#6b7280;">
                        Price:
                        ${{ number_format($request->service->price ?? 0, 2) }}
                    </p>
                </div>

                <a href="{{ route('citizen.payments.show', $request) }}"
                   class="btn-primary"
                   style="text-decoration:none;">
                    Pay Now
                </a>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            No payments available.
        </div>
    @endforelse
</div>
@endsection