<?php

namespace App\Mail;

use App\Models\Payment;
use App\Services\PdfGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentDocumentsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
        $this->payment->loadMissing([
            'serviceRequest.service',
            'serviceRequest.office',
            'serviceRequest.citizen',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('ui.mail.payment_documents_title', [
                'ref' => $this->payment->serviceRequest?->reference_number ?? '',
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-documents',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $serviceRequest = $this->payment->serviceRequest;

        if (! $serviceRequest) {
            return [];
        }

        $pdf = app(PdfGenerationService::class);
        $ref = $serviceRequest->reference_number;

        return [
            Attachment::fromData(
                fn () => $pdf->invoicePdfOutput($serviceRequest, $this->payment),
                'invoice-'.$ref.'.pdf',
            )->withMime('application/pdf'),
            Attachment::fromData(
                fn () => $pdf->receiptPdfOutput($serviceRequest, $this->payment),
                'receipt-'.$ref.'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
