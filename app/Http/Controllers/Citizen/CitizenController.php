<?php

namespace App\Http\Controllers\Citizen;

use App\Models\Message;
use App\Models\User;
use App\Mail\ServiceRequestSubmitted;
use Illuminate\Support\Facades\Mail;
use App\Models\Feedback;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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

    public function requestQr(ServiceRequest $serviceRequest)
{
    abort_if($serviceRequest->citizen_id !== Auth::id(), 403);

    $trackingUrl = route('citizen.requests');

    $qrCode = QrCode::size(220)->generate($trackingUrl);

    return view('citizen.request-qr', compact('serviceRequest', 'qrCode'));
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

    Mail::to(Auth::user()->email)
        ->send(new ServiceRequestSubmitted($serviceRequest));

    return redirect()
        ->route('citizen.requests')
        ->with('success', 'Request submitted successfully. Reference number: ' . $serviceRequest->reference_number);
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

    $request->validate([
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

    Message::create([
        'service_request_id' => $serviceRequest->id,
        'sender_id' => Auth::id(),
        'recipient_id' => $staffUser->id,
        'message' => $request->message,
    ]);

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

    return view('citizen.payments', compact('requests'));
}

public function paymentPage(ServiceRequest $serviceRequest)
{
    return view('citizen.payment-show', compact('serviceRequest'));
}

public function processPayment(Request $request, ServiceRequest $serviceRequest)
{
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
        'gateway_reference' => 'LOCAL-' . strtoupper(\Illuminate\Support\Str::random(10)),
        'paid_at' => now(),
    ]);

    return redirect()
        ->route('citizen.payments')
        ->with('success', 'Payment completed successfully.');
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

    $googleMapsApiKey = env('GOOGLE_MAPS_API_KEY');

    return view('citizen.maps', compact('offices', 'googleMapsApiKey'));
}

public function appointments()
{
    $offices = Office::orderBy('name')->get();

    return view('citizen.appointments', compact('offices'));
}

public function createAppointment(Office $office)
{
    return view('citizen.appointment-create', compact('office'));
}

public function storeAppointment(Request $request)
{
    return redirect()
        ->route('citizen.appointments')
        ->with('success', 'Appointment booked successfully.');
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
}