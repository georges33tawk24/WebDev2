@extends('layouts.admin')

@section('title', __('ui.staff.request_details'))
@section('page-title', __('ui.staff.request_details'))

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ __('ui.staff.request_details') }}</div>
        <div class="page-subtitle">{{ __('ui.staff.reference_label', ['ref' => $serviceRequest->reference_number]) }}</div>
    </div>
    <a href="{{ route('staff.requests.index') }}" class="btn-secondary"> {{ __('ui.staff.back_requests') }}</a>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">

    
    <div>
        {{-- Request Info --}}
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.staff.request_info') }}</div>
            <table style="width:100%;">
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px; width:40%;">{{ __('ui.table.citizen') }}</td>
                    <td style="padding:8px 0; font-size:14px; font-weight:500;">{{ $serviceRequest->citizen?->name ?? __('ui.na') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.table.email') }}</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->citizen?->email ?? __('ui.na') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.table.service') }}</td>
                    <td style="padding:8px 0; font-size:14px; font-weight:500;">{{ $serviceRequest->service?->localized('name') ?? __('ui.na') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.table.office') }}</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->office?->localized('name') ?? __('ui.na') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.table.submitted') }}</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->submitted_at ? localized_datetime($serviceRequest->submitted_at, 'M d, Y H:i') : __('ui.na') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.staff.current_status') }}</td>
                    <td style="padding:8px 0;">
                        <x-status-badge :status="$serviceRequest->status" />
                    </td>
                </tr>
                @if($serviceRequest->notes)
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">{{ __('ui.staff.notes') }}</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.staff.documents') }}</div>
            @forelse($serviceRequest->documents as $document)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e5e7eb;">
                <div>
                    <div style="font-size:14px; font-weight:500; color:#111827;">{{ $document->original_name }}</div>
                    <div style="font-size:12px; color:#6b7280;">{{ ucfirst($document->type) }} • {{ localized_digits(__('ui.staff.file_size_kb', ['size' => localized_number($document->size / 1024, 1)])) }}</div>
                </div>
                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn-secondary" style="padding:6px 12px; font-size:12px;">{{ __('ui.download') }}</a>
            </div>
            @empty
            <p style="color:#6b7280; font-size:14px;">{{ __('ui.staff.no_documents') }}</p>
            @endforelse

            {{-- Upload Document --}}
            <div style="margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
                <div style="font-size:14px; font-weight:600; color:#111827; margin-bottom:12px;">{{ __('ui.staff.upload_response_document') }}</div>
                <form method="POST" action="{{ route('staff.requests.uploadDocument', $serviceRequest) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="display:flex; gap:12px; align-items:center;">
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <button type="submit" class="btn-primary" style="white-space:nowrap;">{{ __('ui.upload') }}</button>
                    </div>
                    @error('document') <div class="form-error">{{ $message }}</div> @enderror
                </form>
            </div>
        </div>

       
        <div class="card">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.staff.status_history') }}</div>
            @forelse($serviceRequest->statusHistories as $history)
            <div style="display:flex; gap:12px; padding:10px 0; border-bottom:1px solid #e5e7eb;">
                <div style="width:8px; height:8px; border-radius:50%; background:#1a56db; margin-top:6px; flex-shrink:0;"></div>
                <div>
                    <div style="font-size:13px; font-weight:600; color:#111827;">
                        {{ $history->from_status ? __('ui.status.'.$history->from_status) : __('ui.staff.status_created') }} →
                        {{ __('ui.status.'.$history->to_status) }}
                    </div>
                    <div style="font-size:12px; color:#6b7280;">
                        {{ __('ui.staff.changed_by', ['name' => $history->changedBy?->name ?? __('ui.staff.system')]) }} •
                        {{ $history->changed_at?->format('M d, Y H:i') ?? '' }}
                    </div>
                    @if($history->comment)
                    <div style="font-size:13px; color:#374151; margin-top:4px;">{{ $history->comment }}</div>
                    @endif
                </div>
            </div>
            @empty
            <p style="color:#6b7280; font-size:14px;">{{ __('ui.staff.no_status_changes') }}</p>
            @endforelse
        </div>
    </div>

   
    <div>
        <div class="card">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">{{ __('ui.staff.update_status') }}</div>
            <form method="POST" action="{{ route('staff.requests.updateStatus', $serviceRequest) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="form-label">{{ __('ui.staff.new_status') }}</label>
                    <x-status-select name="status" :selected="$serviceRequest->status" />
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('ui.staff.comment_optional') }}</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="{{ __('ui.staff.comment_placeholder') }}"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">{{ __('ui.staff.update_status') }}</button>
            </form>
        </div>
    </div>

</div>
@endsection