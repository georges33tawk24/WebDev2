<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function index(): View
    {
        $requests = ServiceRequest::query()
            ->with(['citizen', 'service', 'office'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $serviceRequest->load([
            'citizen',
            'service',
            'office',
            'statusHistories.changedBy',
            'documents',
            'payments',
        ]);

        return view('admin.requests.show', compact('serviceRequest'));
    }
}
