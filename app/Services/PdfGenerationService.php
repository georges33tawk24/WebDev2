<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGenerationService
{
    public function receiptPdfOutput(ServiceRequest $serviceRequest, Payment $payment): string
    {
        $this->loadPaymentPdfRelations($serviceRequest);

        return Pdf::loadView('citizen.receipt-pdf', [
            'serviceRequest' => $serviceRequest,
            'payment' => $payment,
        ])->output();
    }

    public function invoicePdfOutput(ServiceRequest $serviceRequest, Payment $payment): string
    {
        $this->loadPaymentPdfRelations($serviceRequest);

        return Pdf::loadView('pdfs.invoice', [
            'serviceRequest' => $serviceRequest,
            'payment' => $payment,
        ])->output();
    }

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
        $this->loadPaymentPdfRelations($serviceRequest);

        $pdf = Pdf::loadView('pdfs.receipt', [
            'serviceRequest' => $serviceRequest,
            'payment' => $serviceRequest->latestPaidPayment(),
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

    private function loadPaymentPdfRelations(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing([
            'service',
            'office',
            'citizen',
            'payments',
            'statusHistories' => fn ($query) => $query->orderBy('changed_at'),
        ]);
    }
}