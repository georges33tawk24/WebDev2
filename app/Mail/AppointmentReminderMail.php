<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public int $hoursBefore,
        public bool $forStaff = false,
    ) {
        $this->appointment->loadMissing(['office', 'citizen', 'staff']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('ui.mail.appointment_reminder_subject', ['hours' => $this->hoursBefore]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
