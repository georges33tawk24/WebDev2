<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGenerationService
{
    public function generateApprovalCertificate(ServiceRequest $serviceRequest): string
    {
        $pdf = Pdf::loadView('pdfs.approval-certificate', [
            'serviceRequest' => $serviceRequest,
        ]);

        $filename = 'approval-certificate-' . $serviceRequest->reference_number . '.pdf';
        $path = 'generated-pdfs/' . $filename;

        \Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    public function generateReceipt(ServiceRequest $serviceRequest): string
    {
        $pdf = Pdf::loadView('pdfs.receipt', [
            'serviceRequest' => $serviceRequest,
        ]);

        $filename = 'receipt-' . $serviceRequest->reference_number . '.pdf';
        $path = 'generated-pdfs/' . $filename;

        \Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    public function generateResponseLetter(ServiceRequest $serviceRequest): string
    {
        $pdf = Pdf::loadView('pdfs.response-letter', [
            'serviceRequest' => $serviceRequest,
        ]);

        $filename = 'response-letter-' . $serviceRequest->reference_number . '.pdf';
        $path = 'generated-pdfs/' . $filename;

        \Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}