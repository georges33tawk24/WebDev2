@extends('layouts.admin')

@section('title', 'Card Payment')
@section('page-title', 'Card Payment')

@section('content')
<div class="card" style="max-width:850px; margin:auto;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; margin-bottom:26px;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">
                Secure Card Checkout
            </h1>

            <p style="color:#6b7280; line-height:1.7;">
                Complete your government service payment using the simulated secure card gateway.
            </p>
        </div>

        <a href="{{ route('citizen.crypto.payments.show', $serviceRequest) }}"
           class="btn-secondary"
           style="text-decoration:none;">
            Switch to Crypto
        </a>
    </div>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:12px; margin-bottom:22px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:16px; padding:22px; margin-bottom:26px;">
        <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
        <p><strong>Reference:</strong> {{ $serviceRequest->reference_number }}</p>
        <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'N/A' }}</p>
        <p style="font-size:20px; font-weight:700; margin-bottom:0;">
            Amount: ${{ number_format($serviceRequest->service->price ?? 0, 2) }}
        </p>
    </div>

    <form method="POST" action="{{ route('citizen.payments.process', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-weight:700;">Card Holder Name</label>
            <input type="text" name="card_holder" required placeholder="John Doe"
                   style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:14px; margin-top:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:700;">Card Number</label>
            <input type="text" name="card_number" required maxlength="16" placeholder="1234567890123456"
                   style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:14px; margin-top:8px;">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:26px;">
            <div>
                <label style="font-weight:700;">Expiry Date</label>
                <input type="text" name="expiry_date" required placeholder="MM/YY"
                       style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:14px; margin-top:8px;">
            </div>

            <div>
                <label style="font-weight:700;">CVV</label>
                <input type="text" name="cvv" required maxlength="3" placeholder="123"
                       style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:14px; margin-top:8px;">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;">
            <a href="{{ route('citizen.payments') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                Back to Payments
            </a>

            <button type="submit" class="btn-primary">
                Complete Card Payment
            </button>
        </div>
    </form>
</div>
@endsection