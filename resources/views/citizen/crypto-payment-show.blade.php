@extends('layouts.admin')

@section('title', 'Crypto Payment')
@section('page-title', 'Crypto Payment')

@section('content')
<div class="card" style="max-width:1000px; margin:auto;">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap; margin-bottom:28px;">
        <div>
            <h1 style="font-size:30px; font-weight:700; margin-bottom:8px;">
                Cryptocurrency Payment
            </h1>

            <p style="color:#6b7280; line-height:1.7; max-width:680px;">
                Pay government service fees using a crypto-style payment workflow.
                This demo simulates wallet assignment, exchange conversion, transaction confirmation,
                and blockchain reference storage.
            </p>
        </div>

        <a href="{{ route('citizen.payments') }}"
           class="btn-secondary"
           style="text-decoration:none;">
            Back to Payments
        </a>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:22px;">
            {{ session('success') }}
        </div>
    @endif

    @php
        $latestCryptoPayment = $serviceRequest->payments
            ->where('method', 'crypto')
            ->sortByDesc('created_at')
            ->first();

        $amountUsd = $serviceRequest->service->price ?? 0;
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        <div style="border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
            <h2 style="font-size:20px; font-weight:700; margin-bottom:18px;">
                Request Summary
            </h2>

            <p><strong>Reference:</strong> {{ $serviceRequest->reference_number }}</p>
            <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
            <p><strong>Office:</strong> {{ $serviceRequest->office->name ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> ${{ number_format($amountUsd, 2) }}</p>
        </div>

        <div style="border:1px solid #e5e7eb; border-radius:18px; padding:24px; background:#f9fafb;">
            <h2 style="font-size:20px; font-weight:700; margin-bottom:18px;">
                Estimated Exchange Rates
            </h2>

            @foreach($rates as $symbol => $rate)
                <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                    <span>{{ $symbol }}</span>
                    <strong>
                        {{ number_format($amountUsd / $rate, 8) }} {{ $symbol }}
                    </strong>
                </div>
            @endforeach
        </div>

    </div>

    <div style="margin-top:26px; border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
        <h2 style="font-size:20px; font-weight:700; margin-bottom:18px;">
            Choose Crypto Currency
        </h2>

        <form method="POST" action="{{ route('citizen.crypto.payments.process', $serviceRequest) }}">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:22px;">
                @foreach(['BTC' => 'Bitcoin', 'ETH' => 'Ethereum', 'USDT' => 'Tether'] as $symbol => $name)
                    <label style="border:1px solid #d1d5db; border-radius:16px; padding:18px; cursor:pointer;">
                        <input type="radio" name="crypto_currency" value="{{ $symbol }}" required>
                        <div style="font-weight:700; margin-top:10px;">{{ $name }}</div>
                        <div style="color:#6b7280; font-size:14px;">{{ $symbol }}</div>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="btn-primary">
                Generate Crypto Payment
            </button>
        </form>
    </div>

    @if($latestCryptoPayment)
        <div style="margin-top:26px; border:1px solid #bfdbfe; background:#eff6ff; border-radius:18px; padding:24px;">
            <h2 style="font-size:20px; font-weight:700; margin-bottom:18px; color:#1e3a8a;">
                Active Crypto Payment
            </h2>

            <p><strong>Status:</strong> {{ ucfirst($latestCryptoPayment->status) }}</p>
            <p><strong>Currency:</strong> {{ $latestCryptoPayment->crypto_currency }}</p>
            <p><strong>Crypto Amount:</strong> {{ $latestCryptoPayment->crypto_amount }} {{ $latestCryptoPayment->crypto_currency }}</p>
            <p style="word-break:break-all;"><strong>Wallet:</strong> {{ $latestCryptoPayment->wallet_address }}</p>
            <p><strong>Reference:</strong> {{ $latestCryptoPayment->gateway_reference }}</p>

            @if($latestCryptoPayment->transaction_hash)
                <p style="word-break:break-all;">
                    <strong>Transaction Hash:</strong> {{ $latestCryptoPayment->transaction_hash }}
                </p>
            @endif

            @if($latestCryptoPayment->status !== 'paid')
                <form method="POST" action="{{ route('citizen.crypto.payments.confirm', $latestCryptoPayment) }}" style="margin-top:20px;">
                    @csrf
                    <button type="submit" class="btn-primary">
                        Simulate Blockchain Confirmation
                    </button>
                </form>
            @else
                <div style="margin-top:18px; background:#dcfce7; color:#166534; padding:14px; border-radius:12px;">
                    Payment confirmed successfully.
                </div>
            @endif
        </div>
    @endif

</div>
@endsection