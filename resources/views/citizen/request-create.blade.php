@extends('layouts.admin')

@section('title', 'Submit Request')
@section('page-title', 'Submit Request')

@section('content')
<div class="card">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        Submit Service Request
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        You are applying for: <strong>{{ $service->name }}</strong>
    </p>

    <div class="card" style="background:#f9fafb; margin-bottom:24px;">
        <h2 style="font-size:18px; font-weight:700; margin-bottom:10px;">Service Summary</h2>

        <p><strong>Office:</strong> {{ $service->office->name ?? 'N/A' }}</p>
        <p><strong>Category:</strong> {{ $service->category->name ?? 'N/A' }}</p>
        <p><strong>Price:</strong> ${{ number_format($service->price, 2) }}</p>
        <p>
            <strong>Duration:</strong>
            {{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' minutes' : 'N/A' }}
        </p>
    </div>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:14px; border-radius:10px; margin-bottom:20px;">
            <strong>Please fix the following errors:</strong>
            <ul style="margin-top:8px; padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('citizen.requests.store') }}"
          enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="service_id" value="{{ $service->id }}">

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                Additional Information / Notes
            </label>

            <textarea name="notes"
                      rows="5"
                      placeholder="Write any information related to your request..."
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('notes') }}</textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                Upload Required Documents
            </label>

            <input type="file"
                   name="documents[]"
                   multiple
                   style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px; background:white;">

            <p style="color:#6b7280; font-size:14px; margin-top:8px;">
                Allowed files: PDF, JPG, PNG, DOC, DOCX. Max size: 5MB each.
            </p>
        </div>

        <div style="display:flex; gap:12px; margin-top:28px;">
            <a href="{{ route('citizen.services.show', $service) }}"
               class="btn-secondary"
               style="text-decoration:none;">
                Back
            </a>

            <button type="submit" class="btn-primary">
                Submit Request
            </button>
        </div>
    </form>
</div>
@endsection