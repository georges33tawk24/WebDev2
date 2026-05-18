@extends('layouts.staff')

@section('title', 'Request Details')
@section('page-title', 'Request Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Request Details</div>
        <div class="page-subtitle">Reference: {{ $serviceRequest->reference_number }}</div>
    </div>
    <a href="{{ route('staff.requests.index') }}" class="btn-secondary"> Back to Requests</a>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">

    
    <div>
        {{-- Request Info --}}
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Request Information</div>
            <table style="width:100%;">
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px; width:40%;">Citizen</td>
                    <td style="padding:8px 0; font-size:14px; font-weight:500;">{{ $serviceRequest->citizen?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Email</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->citizen?->email ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Service</td>
                    <td style="padding:8px 0; font-size:14px; font-weight:500;">{{ $serviceRequest->service?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Office</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->office?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Submitted</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->submitted_at?->format('M d, Y H:i') ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Current Status</td>
                    <td style="padding:8px 0;">
                        @php $status = $serviceRequest->status; @endphp
                        <span class="badge badge-{{ str_replace('_', '-', $status) }}">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </td>
                </tr>
                @if($serviceRequest->notes)
                <tr>
                    <td style="padding:8px 0; color:#6b7280; font-size:13px;">Notes</td>
                    <td style="padding:8px 0; font-size:14px;">{{ $serviceRequest->notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Documents</div>
            @forelse($serviceRequest->documents as $document)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e5e7eb;">
                <div>
                    <div style="font-size:14px; font-weight:500; color:#111827;">{{ $document->original_name }}</div>
                    <div style="font-size:12px; color:#6b7280;">{{ ucfirst($document->type) }} • {{ number_format($document->size / 1024, 1) }} KB</div>
                </div>
                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Download</a>
            </div>
            @empty
            <p style="color:#6b7280; font-size:14px;">No documents uploaded yet.</p>
            @endforelse

            {{-- Upload Document --}}
            <div style="margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
                <div style="font-size:14px; font-weight:600; color:#111827; margin-bottom:12px;">Upload Response Document</div>
                <form method="POST" action="{{ route('staff.requests.uploadDocument', $serviceRequest) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="display:flex; gap:12px; align-items:center;">
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <button type="submit" class="btn-primary" style="white-space:nowrap;">Upload</button>
                    </div>
                    @error('document') <div class="form-error">{{ $message }}</div> @enderror
                </form>
            </div>
        </div>

       
        <div class="card">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Status History</div>
            @forelse($serviceRequest->statusHistories as $history)
            <div style="display:flex; gap:12px; padding:10px 0; border-bottom:1px solid #e5e7eb;">
                <div style="width:8px; height:8px; border-radius:50%; background:#1a56db; margin-top:6px; flex-shrink:0;"></div>
                <div>
                    <div style="font-size:13px; font-weight:600; color:#111827;">
                        {{ ucfirst(str_replace('_', ' ', $history->from_status ?? 'Created')) }} →
                        {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                    </div>
                    <div style="font-size:12px; color:#6b7280;">
                        By {{ $history->changedBy?->name ?? 'System' }} •
                        {{ $history->changed_at?->format('M d, Y H:i') ?? '' }}
                    </div>
                    @if($history->comment)
                    <div style="font-size:13px; color:#374151; margin-top:4px;">{{ $history->comment }}</div>
                    @endif
                </div>
            </div>
            @empty
            <p style="color:#6b7280; font-size:14px;">No status changes yet.</p>
            @endforelse
        </div>
    </div>

   
    <div>
        <div class="card">
            <div style="font-size:16px; font-weight:700; color:#111827; margin-bottom:16px;">Update Status</div>
            <form method="POST" action="{{ route('staff.requests.updateStatus', $serviceRequest) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-control">
                        <option value="pending" {{ $serviceRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_review" {{ $serviceRequest->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="missing_documents" {{ $serviceRequest->status == 'missing_documents' ? 'selected' : '' }}>Missing Documents</option>
                        <option value="approved" {{ $serviceRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $serviceRequest->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ $serviceRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Comment (optional)</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..."></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">Update Status</button>
            </form>
        </div>
    </div>

</div>
@endsection