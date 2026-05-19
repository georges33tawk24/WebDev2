<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send email, SMS, and push reminders before scheduled appointments';

    public function handle(AppointmentReminderService $reminders): int
    {
        $tolerance = (int) config('services.appointments.reminder_tolerance_minutes', 10);
        $sent = 0;

        foreach (config('services.appointments.reminder_windows', []) as $window) {
            $hours = (int) ($window['hours'] ?? 0);
            $column = (string) ($window['column'] ?? '');

            if ($hours <= 0 || $column === '') {
                continue;
            }

            $target = now()->addHours($hours);

            $appointments = Appointment::query()
                ->where('status', 'scheduled')
                ->whereNull($column)
                ->whereBetween('starts_at', [
                    $target->copy()->subMinutes($tolerance),
                    $target->copy()->addMinutes($tolerance),
                ])
                ->get();

            foreach ($appointments as $appointment) {
                $reminders->sendReminder($appointment, $hours);
                $appointment->update([$column => now()]);
                $sent++;
            }
        }

        $this->components->info("Sent {$sent} appointment reminder(s).");

        return self::SUCCESS;
    }
}
