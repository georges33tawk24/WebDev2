<?php

namespace App\Http\Controllers\Citizen;

use App\Events\MessageSent;
use App\Events\RequestSubmitted;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use App\Mail\ServiceRequestSubmitted;
use Illuminate\Support\Facades\Mail;
use App\Models\Feedback;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Mail\ServiceRequestSubmitted;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\Document;
use App\Models\Feedback;
use App\Models\Message;
use App\Models\Office;
use App\Models\Payment;
use App\Models\RequestStatusHistory;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CitizenController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $activeRequests = ServiceRequest::with(['service', 'office', 'payments'])
            ->where('citizen_id', $userId)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = ServiceRequest::with(['service', 'office', 'payments'])
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
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
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
        $requests = ServiceRequest::with(['service', 'office', 'statusHistories', 'payments'])
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

        Mail::to(Auth::user()->email)->send(new ServiceRequestSubmitted($serviceRequest));

        $staffUsers = User::whereHas('role', function ($query) {
                $query->where('slug', 'office_staff');
            })
            ->where('office_id', $serviceRequest->office_id)
            ->get();

        foreach ($staffUsers as $staff) {
            NotificationService::send(
                $staff->id,
                'New Service Request',
                Auth::user()->name . ' submitted a new request for ' . ($serviceRequest->service->name ?? 'a service') . '.',
                'request',
                route('staff.requests.show', $serviceRequest)
            );
        }

        broadcast(new RequestSubmitted($serviceRequest));

        return redirect()
            ->route('citizen.requests')
            ->with('success', 'Request submitted successfully. Reference number: ' . $serviceRequest->reference_number);
    }

    public function requestQr(ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $qrRecord = $serviceRequest->qrCodes()->first();

        if (!$qrRecord) {
            $token = Str::uuid()->toString();
            $trackingUrl = route('public.track', $token);

            $qrImage = QrCode::format('png')
                ->size(300)
                ->generate($trackingUrl);

            $fileName = 'qr-codes/' . $token . '.png';

            Storage::disk('public')->put($fileName, $qrImage);

            $qrRecord = $serviceRequest->qrCodes()->create([
                'token' => $token,
                'image_path' => $fileName,
                'expires_at' => null,
            ]);
        }

        return view('citizen.request-qr', [
            'serviceRequest' => $serviceRequest,
            'qrCodePath' => asset('storage/' . $qrRecord->image_path),
            'trackingUrl' => route('public.track', $qrRecord->token),
        ]);
    }

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

        Feedback::updateOrCreate(
            [
                'service_request_id' => $serviceRequest->id,
                'citizen_id' => Auth::id(),
            ],
            [
                'office_id' => $serviceRequest->office_id,
                'service_id' => $serviceRequest->service_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return redirect()
            ->route('citizen.history')
            ->with('success', 'Feedback submitted successfully.');
    }

    public function chat(ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $serviceRequest->load(['service', 'office']);

        $messages = Message::with(['sender', 'recipient'])
            ->where('service_request_id', $serviceRequest->id)
            ->orderBy('created_at')
            ->get();

        return view('citizen.chat', compact('serviceRequest', 'messages'));
    }

    public function sendMessage(Request $request, ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $staffUser = User::where('office_id', $serviceRequest->office_id)
            ->whereHas('role', function ($query) {
                $query->where('slug', 'office_staff');
            })
            ->first();

        if (!$staffUser) {
            return back()->with('error', 'No office staff available for this office yet.');
        }

        $message = Message::create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => Auth::id(),
            'recipient_id' => $staffUser->id,
            'message' => $validated['message'],
        ]);

        NotificationService::send(
            $staffUser->id,
            'New Citizen Message',
            Auth::user()->name . ' sent a new message regarding request ' . $serviceRequest->reference_number . '.',
            'chat',
            route('staff.requests.chat', $serviceRequest)
        );

        broadcast(new MessageSent($message));

        return back()->with('success', 'Message sent successfully.');
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
    return redirect()
        ->route('citizen.history')
        ->with('success', __('ui.flash.feedback_submitted'));
}
   public function chat(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        return view('citizen.payments', compact('requests'));
    }

    public function paymentPage(ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $serviceRequest->load(['service', 'office', 'payments']);

        return view('citizen.payment-show', compact('serviceRequest'));
    }

    public function processPayment(Request $request, ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

        $request->validate([
            'card_holder' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'digits:16'],
            'expiry_date' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'digits:3'],
        ]);

        Payment::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => Auth::id(),
            'method' => 'card',
            'amount' => $serviceRequest->service->price ?? 0,
            'currency' => 'USD',
            'status' => 'paid',
            'gateway_reference' => 'LOCAL-' . strtoupper(Str::random(10)),
            'paid_at' => now(),
        ]);

        NotificationService::send(
            Auth::id(),
            'Payment Completed',
            'Your payment for request ' . $serviceRequest->reference_number . ' was completed successfully.',
            'payment',
            route('citizen.history')
        );

        return redirect()
            ->route('citizen.payments')
            ->with('success', 'Payment completed successfully.');
    if (!$staffUser) {
        return back()->with('error', __('ui.flash.no_staff_for_chat'));
    }

    public function maps()
    {
        $offices = Office::select(
            'id',
            'name',
            'address',
            'working_hours',
            'latitude',
            'longitude'
        )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $googleMapsApiKey = config('services.google.maps_key');
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

        return view('citizen.maps', compact('offices', 'googleMapsApiKey'));
    }

    public function appointments()
    {
        $offices = Office::orderBy('name')->get();

        $appointments = Appointment::with(['office', 'serviceRequest.service'])
            ->where('citizen_id', Auth::id())
            ->latest('starts_at')
            ->get();

        return view('citizen.appointments', compact('offices', 'appointments'));
    }

    public function createAppointment(Office $office)
    {
        $serviceRequests = ServiceRequest::with('service')
            ->where('citizen_id', Auth::id())
            ->whereIn('status', ['pending', 'in_review', 'missing_documents', 'approved'])
            ->latest()
            ->get();
    return redirect()
        ->route('citizen.payments')
        ->with('success', __('ui.flash.payment_completed'));
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

        return view('citizen.appointment-create', compact('office', 'serviceRequests'));
    }

    public function storeAppointment(Request $request)
    {
        $validated = $request->validate([
            'office_id' => ['required', 'exists:offices,id'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($validated['service_request_id'])) {
            $belongsToCitizen = ServiceRequest::where('id', $validated['service_request_id'])
                ->where('citizen_id', Auth::id())
                ->exists();

            if (!$belongsToCitizen) {
                return back()->with('error', 'Invalid service request selected.');
            }
        }
    $bookedAppointments = Appointment::query()
        ->with('office')
        ->where('citizen_id', Auth::id())
        ->where('status', 'scheduled')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->get();

    return view('citizen.appointments', compact('offices', 'bookedAppointments'));
}

        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes(30);

        if (Appointment::hasConflict($validated['office_id'], $startsAt->toDateTimeString(), $endsAt->toDateTimeString())) {
            return back()
                ->withInput()
                ->with('error', 'This time slot is already booked. Please choose another time.');
        }

        Appointment::create([
            'service_request_id' => $validated['service_request_id'] ?? null,
            'office_id' => $validated['office_id'],
            'citizen_id' => Auth::id(),
            'staff_id' => null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        $staffUsers = User::whereHas('role', function ($query) {
                $query->where('slug', 'office_staff');
            })
            ->where('office_id', $validated['office_id'])
            ->get();

        foreach ($staffUsers as $staff) {
            NotificationService::send(
                $staff->id,
                'New Appointment Booked',
                Auth::user()->name . ' booked an appointment for ' . $startsAt->format('d M Y - h:i A') . '.',
                'appointment',
                route('staff.requests.index')
            );
        }

        NotificationService::send(
            Auth::id(),
            'Appointment Confirmed',
            'Your appointment was booked for ' . $startsAt->format('d M Y - h:i A') . '.',
            'appointment',
            route('citizen.appointments')
        );

        return redirect()
            ->route('citizen.appointments')
            ->with('success', 'Appointment booked successfully.');
    }

    public function history()
    {
        $requests = ServiceRequest::with(['service', 'office', 'payments'])
            ->where('citizen_id', Auth::id())
            ->latest()
            ->get();

        return view('citizen.history', compact('requests'));
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

    Notification::query()->create([
        'user_id' => Auth::id(),
        'title' => __('ui.flash.appointment_booked'),
        'body' => __('ui.citizen.appointment_confirmed_body', [
            'office' => $office?->localized('name') ?? __('ui.na'),
            'when' => localized_datetime($startsAt),
        ]),
        'data' => ['appointment_id' => $appointment->id, 'office_id' => $office?->id],
    ]);

    return redirect()
        ->route('citizen.appointments')
        ->with('success', __('ui.flash.appointment_booked'));
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
            return back()->with('error', 'No paid receipt available for this request.');
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

        $document = Document::where('service_request_id', $serviceRequest->id)
            ->latest()
            ->first();

        if (!$document) {
            return back()->with('error', 'No document available for this request.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function cryptoPaymentPage(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $serviceRequest->load(['service', 'office', 'payments']);

    $rates = [
        'BTC' => 65000,
        'ETH' => 3500,
        'USDT' => 1,
    ];

    return view('citizen.crypto-payment-show', compact('serviceRequest', 'rates'));
}

public function processCryptoPayment(Request $request, ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $validated = $request->validate([
        'crypto_currency' => ['required', 'in:BTC,ETH,USDT'],
    ]);

    $amountUsd = $serviceRequest->service->price ?? 0;

    $rates = [
        'BTC' => 65000,
        'ETH' => 3500,
        'USDT' => 1,
    ];

    $cryptoCurrency = $validated['crypto_currency'];
    $cryptoAmount = $amountUsd / $rates[$cryptoCurrency];
    if (!$payment) {
        return back()->with('error', __('ui.flash.no_receipt'));
    }

    $wallets = [
        'BTC' => 'bc1qwebdev2municipalitydemo8x5p9r4q',
        'ETH' => '0xWebDev2MunicipalityDemoWallet8842',
        'USDT' => 'TWebDev2MunicipalityUSDTWallet92A',
    ];

    $payment = Payment::create([
        'service_request_id' => $serviceRequest->id,
        'user_id' => Auth::id(),
        'method' => 'crypto',
        'amount' => $amountUsd,
        'currency' => 'USD',
        'status' => 'pending',
        'gateway_reference' => 'CRYPTO-' . strtoupper(Str::random(10)),
        'crypto_currency' => $cryptoCurrency,
        'crypto_amount' => round($cryptoAmount, 8),
        'wallet_address' => $wallets[$cryptoCurrency],
    ]);

    NotificationService::send(
        Auth::id(),
        'Crypto Payment Created',
        'A crypto payment request was created. Complete the transaction to confirm your payment.',
        'payment',
        route('citizen.crypto.payments.show', $serviceRequest)
    );

    return redirect()
        ->route('citizen.crypto.payments.show', $serviceRequest)
        ->with('success', 'Crypto payment request created. Please complete the transaction.');
}

public function confirmCryptoPayment(Payment $payment)
{
    abort_if($payment->user_id !== Auth::id(), 403);
    abort_if($payment->method !== 'crypto', 403);

    $payment->update([
        'status' => 'paid',
        'transaction_hash' => '0x' . strtolower(Str::random(64)),
        'confirmed_at' => now(),
        'paid_at' => now(),
    ]);

    NotificationService::send(
        Auth::id(),
        'Crypto Payment Confirmed',
        'Your crypto payment was confirmed successfully.',
        'payment',
        route('citizen.history')
    );

    return redirect()
        ->route('citizen.history')
        ->with('success', 'Crypto payment confirmed successfully.');
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