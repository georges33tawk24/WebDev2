<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public bool $forStaff = false,
    ) {
        $this->appointment->loadMissing(['office', 'citizen', 'staff']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('ui.mail.appointment_booked_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-booked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
