@extends('layouts.admin')

@section('title', __('ui.citizen.history_title'))
@section('page-title', __('ui.citizen.history_title'))

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">{{ __('ui.citizen.history_title') }}</h1>
    <p style="color:#6b7280; margin-bottom:24px;">{{ __('ui.citizen.history_sub') }}</p>

    @forelse($requests as $request)
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:20px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; gap:20px;">
                <div>
                    <h2 style="font-size:20px; font-weight:700;">
                        {{ $request->service?->localized('name') ?? __('ui.citizen.service_removed') }}
                    </h2>

                    <p style="color:#6b7280;">{{ __('ui.citizen.reference_colon') }} {{ $request->reference_number }}</p>
                    <p style="color:#6b7280;">{{ __('ui.citizen.office_colon') }} {{ $request->office?->localized('name') ?? __('ui.na') }}</p>
                    <p style="color:#6b7280;">{{ __('ui.citizen.submitted_colon') }} {{ $request->created_at ? localized_date($request->created_at) : __('ui.na') }}</p>
                    <p style="color:#6b7280;">{{ __('ui.citizen.status_colon') }} {{ __('ui.status.'.$request->status) }}</p>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a href="{{ route('citizen.history.receipt', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; text-align:center;">
                        {{ __('ui.citizen.download_receipt') }}
                    </a>

                    <a href="{{ route('citizen.history.document', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; text-align:center;">
                        {{ __('ui.citizen.download_document') }}
                    </a>

                    <a href="{{ route('citizen.feedback.create', $request) }}"
                       class="btn-secondary"
                       style="text-decoration:none; text-align:center;">
                        {{ __('ui.citizen.leave_feedback') }}
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:40px; color:#6b7280;">
            {{ __('ui.citizen.no_history') }}
        </div>
    @endforelse
</div>
@endsection
