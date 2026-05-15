<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
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

        $activeRequests = ServiceRequest::with(['service', 'office'])
            ->where('citizen_id', $userId)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = ServiceRequest::with(['service', 'office'])
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

    public function showService(Service $service)
    {
        $service->load(['office', 'category']);

        return view('citizen.service-show', compact('service'));
    }

    public function requests()
    {
        $requests = ServiceRequest::with(['service', 'office', 'statusHistories'])
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

        return redirect()
            ->route('citizen.requests')
            ->with('success', 'Request submitted successfully. Reference number: ' . $serviceRequest->reference_number);
    }
    public function payments()
{
    $requests = ServiceRequest::with(['service', 'office'])
        ->where('citizen_id', Auth::id())
        ->latest()
        ->get();

    return view('citizen.payments', compact('requests'));
}

public function paymentPage(ServiceRequest $serviceRequest)
{
    return view('citizen.payment-show', compact('serviceRequest'));
}

public function processPayment(ServiceRequest $serviceRequest)
{
    return redirect()
        ->route('citizen.payments')
        ->with('success', 'Payment completed successfully.');
}

public function maps()
{
    $offices = Office::all();

    return view('citizen.maps', compact('offices'));
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
}