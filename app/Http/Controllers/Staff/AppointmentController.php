<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\LiveUpdateService;
use App\Services\NotificationService;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId, 403);

        $appointments = Appointment::query()
            ->with(['citizen', 'serviceRequest.service'])
            ->where('office_id', $officeId)
            ->orderByDesc('starts_at')
            ->paginate(20);

        return view('staff.appointments.index', compact('appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId && (int) $appointment->office_id === (int) $officeId, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled,rescheduled'],
        ]);

        $appointment->update(['status' => $validated['status']]);

        $citizen = $appointment->citizen;

        if ($citizen) {
            $body = __('ui.notifications.appointment_status_body', [
                'status' => __('ui.status.'.$validated['status']),
                'when' => localized_datetime($appointment->starts_at),
            ]);

            app(NotificationService::class)->appointmentStatusUpdated(
                $citizen,
                $validated['status'],
                $appointment->starts_at,
                $appointment->id,
            );

            app(SmsService::class)->send($citizen, $body);
            app(LiveUpdateService::class)->bump($citizen);
        }

        return back()->with('success', __('ui.flash.appointment_updated'));
    }
}
