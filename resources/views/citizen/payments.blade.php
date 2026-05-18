@extends('layouts.admin')

@section('title', __('ui.citizen.payments_title'))
@section('page-title', __('ui.citizen.payments_title'))

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:28px; font-weight:700;">{{ __('ui.citizen.payments_title') }}</h1>
            <p style="color:#6b7280;">{{ __('ui.citizen.manage_payments_sub') }}</p>
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
                        {{ $request->service?->localized('name') ?? __('ui.citizen.service_removed') }}
                    </h2>

                    <p style="color:#6b7280;">
                        {{ __('ui.citizen.ref') }}: {{ $request->reference_number }}
                    </p>

                    <p style="color:#6b7280;">
                        {{ __('ui.table.price') }}:
                        {{ localized_money($request->service->price ?? 0) }}
                    </p>
                </div>

                <a href="{{ route('citizen.payments.show', $request) }}"
                   class="btn-primary"
                   style="text-decoration:none;">
                    {{ __('ui.citizen.pay_now') }}
                </a>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            {{ __('ui.citizen.no_payments') }}
        </div>
    @endforelse
</div>
@endsection