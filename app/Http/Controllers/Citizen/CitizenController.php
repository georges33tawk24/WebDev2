<?php

namespace App\Http\Controllers\Citizen;

use App\Models\Appointment;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use App\Services\ExchangeRateService;
use App\Services\NotificationService;
use App\Services\PaymentDocumentService;
use App\Services\RequestStatusHistoryService;
use App\Services\RequestTrackingService;
use App\Services\SmsService;
use Carbon\Carbon;
use App\Mail\ServiceRequestSubmitted;
use Illuminate\Support\Facades\Mail;
use App\Models\Feedback;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Office;
use App\Models\RequestStatusHistory;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CitizenController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $activeRequests = ServiceRequest::with(['service', 'office','payments'])
            ->where('citizen_id', $userId)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = ServiceRequest::with(['service', 'office','payments'])
            ->where('citizen_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $totalRequests = ServiceRequest::where('citizen_id', $userId)->count();
        $pendingRequests = ServiceRequest::where('citizen_id', $userId)->where('status', 'pending')->count();
        $completedRequests = ServiceRequest::where('citizen_id', $userId)->where('status', 'completed')->count();

        return view('citizen.dashboard', compact(
            'activeRequests',
            'recentRequests',
            'totalRequests',
            'pendingRequests',
            'completedRequests'
        ));
    }

    public function services(Request $request)
    {
        $services = Service::with(['office', 'category'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->office_id, function ($query, $officeId) {
                $query->where('office_id', $officeId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $offices = Office::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('citizen.services', compact('services', 'offices', 'categories'));
    }

    public function requestQr(ServiceRequest $serviceRequest, RequestTrackingService $trackingService)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $trackingUrl = $trackingService->trackingUrl($serviceRequest);
        $qrCode = QrCodeGenerator::size(220)->generate($trackingUrl);

        return view('citizen.request-qr', compact('serviceRequest', 'qrCode', 'trackingUrl'));
    }

   public function showService(Service $service)
{
    $service->load(['office', 'category']);

    $feedbacks = Feedback::with(['citizen', 'serviceRequest'])
        ->whereHas('serviceRequest', function ($query) use ($service) {
            $query->where('service_id', $service->id);
        })
        ->latest()
        ->get();

    $averageRating = $feedbacks->count() > 0
        ? round($feedbacks->avg('rating'), 1)
        : null;

    return view('citizen.service-show', compact('service', 'feedbacks', 'averageRating'));
}

    public function requests()
    {
        $requests = ServiceRequest::with(['service', 'office', 'statusHistories','payments'])
            ->where('citizen_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('citizen.requests', compact('requests'));
    }

    public function createRequest(Service $service)
    {
        $service->load(['office', 'category']);

        return view('citizen.request-create', compact('service'));
    }

   public function storeRequest(Request $request)
{
    $request->validate([
        'service_id' => ['required', 'exists:services,id'],
        'notes' => ['nullable', 'string', 'max:2000'],
        'documents.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
    ]);

    $service = Service::findOrFail($request->service_id);

    $serviceRequest = ServiceRequest::create([
        'reference_number' => (string) Str::uuid(),
        'citizen_id' => Auth::id(),
        'service_id' => $service->id,
        'office_id' => $service->office_id,
        'status' => 'pending',
        'notes' => $request->notes,
        'submitted_at' => now(),
    ]);

    RequestStatusHistory::create([
        'service_request_id' => $serviceRequest->id,
        'changed_by' => Auth::id(),
        'from_status' => null,
        'to_status' => 'pending',
        'comment' => 'Request submitted by citizen.',
        'changed_at' => now(),
    ]);

    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            $path = $file->store('citizen-documents', 'public');

            Document::create([
                'service_request_id' => $serviceRequest->id,
                'uploaded_by' => Auth::id(),
                'type' => 'required',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    $serviceRequest->load(['citizen', 'service', 'office']);

    app(RequestTrackingService::class)->ensureQrToken($serviceRequest);

    app(NotificationService::class)->newServiceRequest($serviceRequest);

    Mail::to(Auth::user()->email)
        ->send(new ServiceRequestSubmitted($serviceRequest));

    return redirect()
        ->route('citizen.requests')
        ->with('success', __('ui.flash.request_submitted', ['ref' => $serviceRequest->reference_number]));
}
    public function createFeedback(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $serviceRequest->load(['service', 'office']);

    return view('citizen.feedback-create', compact('serviceRequest'));
}

public function storeFeedback(Request $request, ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $request->validate([
        'rating' => ['required', 'integer', 'min:1', 'max:5'],
        'comment' => ['nullable', 'string', 'max:2000'],
    ]);

    $feedback = Feedback::updateOrCreate(
        [
            'service_request_id' => $serviceRequest->id,
            'citizen_id' => Auth::id(),
        ],
        [
            'office_id' => $serviceRequest->office_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]
    );

    app(NotificationService::class)->newFeedback($feedback);

    return redirect()
        ->route('citizen.history')
        ->with('success', __('ui.flash.feedback_submitted'));
}
    public function chatsIndex(): \Illuminate\View\View
    {
        $requests = ServiceRequest::query()
            ->with(['service', 'office'])
            ->where('citizen_id', Auth::id())
            ->whereHas('messages')
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('recipient_id', Auth::id())->whereNull('read_at');
                },
            ])
            ->withMax('messages as last_message_at', 'created_at')
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('citizen.chats.index', compact('requests'));
    }

    public function chat(ServiceRequest $serviceRequest, ChatService $chatService): \Illuminate\View\View
    {
        $chatService->authorizeView($serviceRequest, Auth::user());
        $chatService->markReadForUser($serviceRequest, Auth::user());

        $serviceRequest->load(['service', 'office']);

        $messages = Message::with(['sender'])
            ->where('service_request_id', $serviceRequest->id)
            ->orderBy('created_at')
            ->get();

        return view('citizen.chat', compact('serviceRequest', 'messages'));
    }

    public function sendMessage(Request $request, ServiceRequest $serviceRequest, ChatService $chatService): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $chatService->send($serviceRequest, $request->user(), $validated['message']);

        return back()->with('success', __('ui.flash.message_sent'));
    }
   public function payments()
{
    $requests = ServiceRequest::with(['service', 'office', 'payments'])
        ->where('citizen_id', Auth::id())
        ->whereDoesntHave('payments', function ($query) {
            $query->where('status', 'paid');
        })
        ->latest()
        ->get();

    return view('citizen.payments', compact('requests'));
}

public function maps()
{
    $offices = Office::query()
        ->select(
            'id',
            'name',
            'name_ar',
            'address',
            'address_ar',
            'working_hours',
            'latitude',
            'longitude'
        )
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get()
        ->map(fn (Office $office) => [
            'id' => $office->id,
            'name' => $office->localized('name'),
            'address' => $office->localized('address'),
            'working_hours' => $office->working_hours,
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
        ]);

    $googleMapsApiKey = config('services.google.maps_key');

    return view('citizen.maps', compact('offices', 'googleMapsApiKey'));
}

public function appointments()
{
    $offices = Office::orderBy('name')->get();

    $bookedAppointments = Appointment::query()
        ->with('office')
        ->where('citizen_id', Auth::id())
        ->where('status', 'scheduled')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->get();

    return view('citizen.appointments', compact('offices', 'bookedAppointments'));
}

public function createAppointment(Office $office)
{
    return view('citizen.appointment-create', compact('office'));
}

public function storeAppointment(Request $request)
{
    $validated = $request->validate([
        'office_id' => ['required', 'exists:offices,id'],
        'appointment_date' => ['required', 'date', 'after_or_equal:today'],
        'appointment_time' => ['required', 'in:'.implode(',', appointment_time_slots())],
        'notes' => ['nullable', 'string', 'max:1000'],
    ]);

    $startsAt = Carbon::parse($validated['appointment_date'].' '.$validated['appointment_time']);
    $endsAt = $startsAt->copy()->addHour();

    $slotTaken = Appointment::query()
        ->where('office_id', $validated['office_id'])
        ->where('starts_at', $startsAt)
        ->where('status', 'scheduled')
        ->exists();

    if ($slotTaken) {
        return back()
            ->withErrors(['appointment_time' => __('ui.flash.appointment_slot_taken')])
            ->withInput();
    }

    $serviceRequest = ServiceRequest::query()
        ->where('citizen_id', Auth::id())
        ->where('office_id', $validated['office_id'])
        ->whereIn('status', ['pending', 'in_review', 'missing_documents', 'approved'])
        ->latest('submitted_at')
        ->first();

    $staff = User::query()
        ->where('office_id', $validated['office_id'])
        ->whereHas('role', fn ($query) => $query->where('slug', 'office_staff'))
        ->first();

    $appointment = Appointment::query()->create([
        'service_request_id' => $serviceRequest?->id,
        'office_id' => $validated['office_id'],
        'citizen_id' => Auth::id(),
        'staff_id' => $staff?->id,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'status' => 'scheduled',
        'notes' => $validated['notes'] ?? null,
    ]);

    $office = Office::query()->find($validated['office_id']);

    $body = __('ui.citizen.appointment_confirmed_body', [
        'office' => $office?->localized('name') ?? __('ui.na'),
        'when' => localized_datetime($startsAt),
    ]);

    $when = localized_datetime($startsAt);
    $notifications = app(NotificationService::class);

    $notifications->appointmentBooked(
        Auth::user(),
        $staff,
        (int) $validated['office_id'],
        $startsAt,
        $appointment->id,
        (int) $validated['office_id'],
    );

    if (filled(Auth::user()->email)) {
        \Illuminate\Support\Facades\Mail::to(Auth::user()->email)
            ->send(new \App\Mail\AppointmentBookedMail($appointment));
    }

    app(SmsService::class)->send(Auth::user(), $body);

    if ($staff && filled($staff->email)) {
        \Illuminate\Support\Facades\Mail::to($staff->email)
            ->send(new \App\Mail\AppointmentBookedMail($appointment, forStaff: true));
    }

    return redirect()
        ->route('citizen.appointments')
        ->with('success', __('ui.flash.appointment_booked'));
}

public function history()
{
    $requests = ServiceRequest::with(['service', 'office'])
        ->where('citizen_id', Auth::id())
        ->latest()
        ->get();

    return view('citizen.history', compact('requests'));
}
public function downloadReceipt(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $serviceRequest->load(['service', 'office', 'payments']);

    $payment = $serviceRequest->payments()
        ->where('status', 'paid')
        ->latest()
        ->first();

    if (!$payment) {
        return back()->with('error', __('ui.flash.no_receipt'));
    }

    $pdf = Pdf::loadView('citizen.receipt-pdf', [
        'serviceRequest' => $serviceRequest,
        'payment' => $payment,
    ]);

    return $pdf->download('receipt-' . $serviceRequest->reference_number . '.pdf');
}

public function downloadDocument(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $document = Document::query()
        ->where('service_request_id', $serviceRequest->id)
        ->orderByRaw("CASE type WHEN 'generated_pdf' THEN 0 WHEN 'response' THEN 1 ELSE 2 END")
        ->latest()
        ->first();

    if (! $document || ! Storage::disk('public')->exists($document->file_path)) {
        return back()->with('error', __('ui.flash.no_document'));
    }

    return Storage::disk('public')->download(
        $document->file_path,
        $document->original_name ?? basename($document->file_path)
    );
}
}