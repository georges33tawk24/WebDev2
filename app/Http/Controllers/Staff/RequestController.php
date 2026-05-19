<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ServiceRequest;
use App\Mail\ServiceRequestStatusUpdated;
use App\Services\LiveUpdateService;
use App\Services\NotificationService;
use App\Services\PdfGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestController extends Controller
{
    public function index()
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId, 403);

        $requests = ServiceRequest::with(['citizen', 'service', 'office'])
            ->where('office_id', $officeId)
            ->latest('submitted_at')
            ->paginate(10);

        return view('staff.requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeStaffOffice($serviceRequest);

        $serviceRequest->load(['citizen', 'service', 'office', 'documents', 'statusHistories.changedBy', 'payments']);

        return view('staff.requests.show', compact('serviceRequest'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeStaffOffice($serviceRequest);

        $validated = $request->validate([
            'status'  => ['required', 'in:pending,in_review,missing_documents,approved,rejected,completed'],
            'comment' => ['nullable', 'string'],
        ]);

        $oldStatus = $serviceRequest->status;
        $newStatus = $validated['status'];

        $serviceRequest->update(['status' => $newStatus]);

        $serviceRequest->statusHistories()->create([
            'changed_by'  => auth()->id(),
            'from_status' => $oldStatus,
            'to_status'   => $newStatus,
            'comment'     => $validated['comment'] ?? null,
            'changed_at'  => now(),
        ]);

        if (in_array($newStatus, ['approved', 'completed'], true)) {
            $pdfService = app(PdfGenerationService::class);

            if ($newStatus === 'approved') {
                $path = $pdfService->generateApprovalCertificate($serviceRequest);
            } else {
                $path = $pdfService->generateResponseLetter($serviceRequest);
            }

            $receiptPath = $pdfService->generateReceipt($serviceRequest);

            $serviceRequest->documents()->create([
                'uploaded_by'   => auth()->id(),
                'type'          => 'generated_pdf',
                'file_path'     => $path,
                'original_name' => basename($path),
                'mime_type'     => 'application/pdf',
                'size'          => Storage::disk('public')->size($path),
            ]);

            $serviceRequest->documents()->create([
                'uploaded_by'   => auth()->id(),
                'type'          => 'generated_pdf',
                'file_path'     => $receiptPath,
                'original_name' => basename($receiptPath),
                'mime_type'     => 'application/pdf',
                'size'          => Storage::disk('public')->size($receiptPath),
            ]);
        }

        $serviceRequest->loadMissing(['citizen', 'service', 'office']);

        app(NotificationService::class)->requestStatusUpdated(
            $serviceRequest,
            $oldStatus,
            $newStatus,
            $validated['comment'] ?? null,
        );

        if ($serviceRequest->citizen?->email) {
            Mail::to($serviceRequest->citizen->email)->send(
                new ServiceRequestStatusUpdated(
                    $serviceRequest,
                    $oldStatus,
                    $validated['comment'] ?? null,
                )
            );
        }

        $live = app(LiveUpdateService::class);

        if ($serviceRequest->citizen) {
            $live->bump($serviceRequest->citizen);
        }

        $live->bumpMany(
            app(NotificationService::class)->officeStaffFor((int) $serviceRequest->office_id),
        );

        return back()->with('success', __('ui.flash.status_updated'));
    }

    public function uploadDocument(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeStaffOffice($serviceRequest);

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

        app(NotificationService::class)->staffDocumentUploaded($serviceRequest);

        return back()->with('success', __('ui.flash.document_uploaded'));
    }

    public function downloadDocument(ServiceRequest $serviceRequest, Document $document): StreamedResponse
    {
        $this->authorizeStaffOffice($serviceRequest);

        abort_unless((int) $document->service_request_id === (int) $serviceRequest->id, 404);

        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $downloadName = $document->original_name ?? basename($document->file_path);

        if ($document->type === 'generated_pdf' && ! str_ends_with(strtolower($downloadName), '.pdf')) {
            $downloadName .= '.pdf';
        }

        return Storage::disk('public')->download($document->file_path, $downloadName);
    }

    private function authorizeStaffOffice(ServiceRequest $serviceRequest): void
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId && (int) $serviceRequest->office_id === (int) $officeId, 404);
    }
}
