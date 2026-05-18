@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; gap:18px; flex-wrap:wrap;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">Payments</h1>
            <p style="color:#6b7280; line-height:1.7;">
                Complete service payments using either traditional card checkout or the simulated crypto payment workflow.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:22px;">
            {{ session('success') }}
        </div>
    @endif

    @forelse($requests as $request)
        @php
            $paidPayment = $request->payments->where('status', 'paid')->first();
            $pendingCrypto = $request->payments
                ->where('method', 'crypto')
                ->where('status', 'pending')
                ->sortByDesc('created_at')
                ->first();
        @endphp

        <div style="border:1px solid #e5e7eb; border-radius:18px; padding:24px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap;">
                <div style="flex:1; min-width:280px;">
                    <h2 style="font-size:22px; font-weight:700; margin-bottom:10px;">
                        {{ $request->service->name ?? 'Service removed' }}
                    </h2>

                    <p style="color:#6b7280; margin-bottom:6px;">
                        Reference: <strong>{{ $request->reference_number }}</strong>
                    </p>

                    <p style="color:#6b7280; margin-bottom:6px;">
                        Office: {{ $request->office->name ?? 'N/A' }}
                    </p>

                    <p style="color:#111827; font-weight:700; margin-bottom:12px;">
                        Amount: ${{ number_format($request->service->price ?? 0, 2) }}
                    </p>

                    @if($paidPayment)
                        <span style="background:#dcfce7; color:#166534; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700;">
                            Paid via {{ ucfirst($paidPayment->method) }}
                        </span>

                        @if($paidPayment->method === 'crypto')
                            <span style="background:#eff6ff; color:#1d4ed8; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700; margin-left:8px;">
                                Blockchain Confirmed
                            </span>
                        @endif
                    @elseif($pendingCrypto)
                        <span style="background:#fef3c7; color:#92400e; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700;">
                            Crypto Pending Confirmation
                        </span>
                    @else
                        <span style="background:#fee2e2; color:#991b1b; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700;">
                            Unpaid
                        </span>
                    @endif
                </div>

                <div style="display:flex; flex-direction:column; gap:12px; min-width:220px;">
                    @if(!$paidPayment)
                        <a href="{{ route('citizen.payments.show', $request) }}"
                           class="btn-primary"
                           style="text-decoration:none; justify-content:center;">
                            Pay by Card
                        </a>

                        <a href="{{ route('citizen.crypto.payments.show', $request) }}"
                           class="btn-secondary"
                           style="text-decoration:none; justify-content:center;">
                            Pay by Crypto
                        </a>
                    @else
                        <a href="{{ route('citizen.history') }}"
                           class="btn-secondary"
                           style="text-decoration:none; justify-content:center;">
                            View in History
                        </a>
                    @endif
                </div>
            </div>

            @if($pendingCrypto)
                <div style="margin-top:20px; background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:16px;">
                    <strong>Pending Crypto Payment:</strong>
                    {{ $pendingCrypto->crypto_amount }} {{ $pendingCrypto->crypto_currency }}
                    awaiting confirmation.
                </div>
            @endif
        </div>
    @empty
        <div style="text-align:center; padding:60px 20px; color:#6b7280;">
            <h2 style="font-size:24px; margin-bottom:10px;">No Pending Payments</h2>
            <p>All available service payments are completed or no payable requests exist yet.</p>
        </div>
    @endforelse
</div>
@endsection