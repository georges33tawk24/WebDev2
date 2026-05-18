<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use App\Services\PdfGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Events\RequestStatusUpdated;
use App\Events\MessageSent;
use App\Models\Message;

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
        $serviceRequest->load([
            'citizen',
            'service',
            'office',
            'documents',
            'statusHistories.changedBy',
        ]);

        return view('staff.requests.show', compact('serviceRequest'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_review,missing_documents,approved,rejected,completed'],
            'comment' => ['nullable', 'string'],
        ]);

        $oldStatus = $serviceRequest->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('success', 'The request already has this status.');
        }

        $serviceRequest->update([
            'status' => $newStatus,
        ]);

        $serviceRequest->statusHistories()->create([
            'changed_by' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'comment' => $validated['comment'] ?? null,
            'changed_at' => now(),
        ]);

        NotificationService::send(
            $serviceRequest->citizen_id,
            'Request Status Updated',
            'Your request status changed from ' . ucwords(str_replace('_', ' ', $oldStatus)) . ' to ' . ucwords(str_replace('_', ' ', $newStatus)) . '.',
            'status_update',
            route('citizen.requests')
        );

        broadcast(new RequestStatusUpdated($serviceRequest, $oldStatus, $newStatus));

        if (in_array($newStatus, ['approved', 'completed'])) {
            $this->generateOfficialDocuments($serviceRequest, $newStatus);
        }

        return back()->with('success', 'Request status updated successfully!');
    }

    public function uploadDocument(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $uploadedFile = $request->file('document');
        $path = $uploadedFile->store('response-documents', 'public');

        $serviceRequest->documents()->create([
            'uploaded_by' => auth()->id(),
            'type' => 'response',
            'file_path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        NotificationService::send(
            $serviceRequest->citizen_id,
            'New Official Document Uploaded',
            'The office uploaded a new response document for your request.',
            'document',
            route('citizen.requests')
        );

        return back()->with('success', 'Document uploaded successfully!');
    }

    private function generateOfficialDocuments(ServiceRequest $serviceRequest, string $newStatus): void
    {
        $pdfService = app(PdfGenerationService::class);

        if ($newStatus === 'approved') {
            $mainPath = $pdfService->generateApprovalCertificate($serviceRequest);
        } else {
            $mainPath = $pdfService->generateResponseLetter($serviceRequest);
        }

        $receiptPath = $pdfService->generateReceipt($serviceRequest);

        $serviceRequest->documents()->create([
            'uploaded_by' => auth()->id(),
            'type' => 'generated_pdf',
            'file_path' => $mainPath,
            'original_name' => basename($mainPath),
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('public')->size($mainPath),
        ]);

        $serviceRequest->documents()->create([
            'uploaded_by' => auth()->id(),
            'type' => 'generated_pdf',
            'file_path' => $receiptPath,
            'original_name' => basename($receiptPath),
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('public')->size($receiptPath),
        ]);
    }
    public function chat(ServiceRequest $serviceRequest)
{
    $serviceRequest->load(['citizen', 'service', 'office']);

    $messages = Message::with('sender')
        ->where('service_request_id', $serviceRequest->id)
        ->orderBy('created_at')
        ->get();

    return view('staff.requests.chat', compact('serviceRequest', 'messages'));
}

public function sendMessage(Request $request, ServiceRequest $serviceRequest)
{
    $validated = $request->validate([
        'message' => ['required', 'string', 'max:3000'],
    ]);

    $message = Message::create([
        'service_request_id' => $serviceRequest->id,
        'sender_id' => auth()->id(),
        'receiver_id' => $serviceRequest->citizen_id,
        'message' => $validated['message'],
        'read_at' => null,
    ]);

    NotificationService::send(
        $serviceRequest->citizen_id,
        'New Message from Office',
        'The office sent you a new message regarding your request.',
        'chat',
        route('citizen.chat', $serviceRequest)
    );

    broadcast(new MessageSent($message));

    return back()->with('success', 'Message sent successfully.');
}
}