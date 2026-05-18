<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $previousStatus,
        public ?string $comment = null,
    ) {
        $this->serviceRequest->loadMissing(['citizen', 'service', 'office']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('ui.mail.request_status_updated_title'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request-status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
