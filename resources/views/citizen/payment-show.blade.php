@extends('layouts.admin')

@section('title', __('ui.citizen.payment_details'))
@section('page-title', __('ui.citizen.payment_details'))

@section('content')
<x-form-page>
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:20px;">
        {{ __('ui.citizen.payment_checkout') }}
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
            <strong>{{ __('ui.table.service') }}:</strong>
            {{ $serviceRequest->service->name ?? __('ui.na') }}
        </p>

        <p>
            <strong>{{ __('ui.citizen.reference_colon') }}</strong>
            {{ $serviceRequest->reference_number }}
        </p>

        <p>
            <strong>{{ __('ui.table.amount') }}:</strong>
            {{ localized_money($serviceRequest->service->price ?? 0) }}
        </p>
    </div>

    <form method="POST"
          action="{{ route('citizen.payments.process', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-weight:600;">{{ __('ui.citizen.card_holder') }}</label>

            <input type="text"
                   name="card_holder"
                   required
                   placeholder="{{ __('ui.placeholders.card_holder') }}"
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600;">{{ __('ui.citizen.card_number') }}</label>

            <input type="text"
                   name="card_number"
                   required
                   maxlength="16"
                   placeholder="{{ __('ui.placeholders.card_number') }}"
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">

            <div>
                <label style="font-weight:600;">{{ __('ui.citizen.expiry_date') }}</label>

                <input type="text"
                       name="expiry_date"
                       required
                       placeholder="{{ __('ui.placeholders.card_expiry') }}"
                       style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
            </div>

            <div>
                <label style="font-weight:600;">{{ __('ui.citizen.cvv') }}</label>

                <input type="text"
                       name="cvv"
                       required
                       maxlength="3"
                       placeholder="{{ __('ui.placeholders.card_cvv') }}"
                       style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; margin-top:8px;">
            </div>

        </div>

        <button type="submit" class="btn-primary">
            {{ __('ui.citizen.complete_payment') }}
        </button>
    </form>
</div>
</x-form-page>
@endsection