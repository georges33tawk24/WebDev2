<?php

namespace App\Services;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

class AppointmentReminderService
{
    public function sendReminder(Appointment $appointment, int $hoursBefore): void
    {
        if ($appointment->status !== 'scheduled') {
            return;
        }

        $appointment->loadMissing(['citizen', 'office', 'staff']);

        $citizen = $appointment->citizen;

        if (! $citizen) {
            return;
        }

        $when = localized_datetime($appointment->starts_at);

        app(NotificationService::class)->notify(
            $citizen,
            'ui.notifications.appointment_reminder_title',
            ['hours' => $hoursBefore],
            'ui.notifications.appointment_reminder_body',
            [
                'office' => ['office_id' => $appointment->office_id],
                'when' => ['datetime' => $appointment->starts_at],
                'hours' => $hoursBefore,
            ],
            [
                'type' => 'appointment',
                'appointment_id' => $appointment->id,
            ],
        );

        $body = __('ui.notifications.appointment_reminder_body', [
            'office' => $appointment->office?->localized('name') ?? __('ui.na'),
            'when' => $when,
            'hours' => $hoursBefore,
        ]);

        if (filled($citizen->email)) {
            Mail::to($citizen->email)->send(new AppointmentReminderMail($appointment, $hoursBefore));
        }

        app(SmsService::class)->send($citizen, $body);

        if ($appointment->staff && filled($appointment->staff->email)) {
            Mail::to($appointment->staff->email)->send(
                new AppointmentReminderMail($appointment, $hoursBefore, forStaff: true),
            );
        }
    }
}
