<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['citizen', 'service', 'office'])
            ->latest()
            ->paginate(10);

        return view('staff.requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['citizen', 'service', 'office', 'documents', 'statusHistories.changedBy']);
        return view('staff.requests.show', compact('serviceRequest'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status'  => ['required', 'in:pending,in_review,missing_documents,approved,rejected,completed'],
            'comment' => ['nullable', 'string'],
        ]);

        $oldStatus = $serviceRequest->status;

        $serviceRequest->update(['status' => $validated['status']]);

        $serviceRequest->statusHistories()->create([
            'changed_by'  => auth()->id(),
            'from_status' => $oldStatus,
            'to_status'   => $validated['status'],
            'comment'     => $validated['comment'] ?? null,
            'changed_at'  => now(),
        ]);

        return back()->with('success', 'Request status updated successfully!');
    }

    public function uploadDocument(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $request->file('document')->store('response-documents', 'public');

        $serviceRequest->documents()->create([
            'uploaded_by'   => auth()->id(),
            'type'          => 'response',
            'file_path'     => $path,
            'original_name' => $request->file('document')->getClientOriginalName(),
            'mime_type'     => $request->file('document')->getMimeType(),
            'size'          => $request->file('document')->getSize(),
        ]);

        return back()->with('success', 'Document uploaded successfully!');
    }
}