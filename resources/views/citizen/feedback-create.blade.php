@extends('layouts.admin')

@section('title', 'Submit Feedback')
@section('page-title', 'Submit Feedback')

@section('content')
<div class="card" style="max-width:700px; margin:auto;">
    <h1 style="font-size:28px; font-weight:700; margin-bottom:8px;">
        Submit Feedback
    </h1>

    <p style="color:#6b7280; margin-bottom:24px;">
        Rate your experience for:
        <strong>{{ $serviceRequest->service->name ?? 'Service' }}</strong>
    </p>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:14px; border-radius:10px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('citizen.feedback.store', $serviceRequest) }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                Rating
            </label>

            <select name="rating"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">
                <option value="">Choose rating</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-weight:600; display:block; margin-bottom:8px;">
                Comment
            </label>

            <textarea name="comment"
                      rows="5"
                      placeholder="Write your feedback..."
                      style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:12px;">{{ old('comment') }}</textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <a href="{{ route('citizen.history') }}"
               class="btn-secondary"
               style="text-decoration:none;">
                Back
            </a>

            <button type="submit" class="btn-primary">
                Submit Feedback
            </button>
        </div>
    </form>
</div>
@endsection