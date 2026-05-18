@extends('layouts.admin')

@section('title', 'Payment')
@section('page-title', 'Payment')

@section('content')
<div class="card" style="max-width:700px; margin:auto;">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:20px;">
        Payment Checkout
    </h1>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:10px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background:#f9fafb; border-radius:12px; padding:20px; margin-bottom:24px;">
        <p>
            <strong>Service:</strong>
            {{ $serviceRequest->service->name ?? 'N/A' }}
        </p>

        <p>
            <strong>Reference:</strong>
            {{ $serviceRequest->reference_number }}
        </p>

        <p>
            <strong>Amount:</strong>
            ${{ number_format($serviceRequest->service->price ?? 0, 2) }}
        </p>
    </div>

    <form method="POST"
          action="{{ route('citizen.payments.process', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-weight:600;">Card Holder Name</label>

            <input type="text"
                   name="card_holder"
                   required
                   placeholder="John Doe"
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600;">Card Number</label>

            <input type="text"
                   name="card_number"
                   required
                   maxlength="16"
                   placeholder="1234567890123456"
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">

            <div>
                <label style="font-weight:600;">Expiry Date</label>

                <input type="text"
                       name="expiry_date"
                       required
                       placeholder="MM/YY"
                       style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
            </div>

            <div>
                <label style="font-weight:600;">CVV</label>

                <input type="text"
                       name="cvv"
                       required
                       maxlength="3"
                       placeholder="123"
                       style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
            </div>

        </div>

        <button type="submit" class="btn-primary">
            Complete Payment
        </button>
    </form>
</div>
@endsection