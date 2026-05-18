<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalOffices  = Office::count();
        $totalUsers    = User::count();
        $totalCitizens = User::whereHas('role', fn($q) => $q->where('slug', 'citizen'))->count();
        $totalStaff    = User::whereHas('role', fn($q) => $q->where('slug', 'office_staff'))->count();
        $totalRequests = ServiceRequest::count();
        $totalRevenue  = ServiceRequest::whereIn('status', ['approved', 'completed'])
            ->join('services', 'service_requests.service_id', '=', 'services.id')
            ->sum('services.price');

        
        $requestsPerOffice = Office::withCount('serviceRequests')->get();

        
        $requestsPerService = Service::withCount('serviceRequests')->get();

        
        $monthlyRequests = ServiceRequest::select(
            DB::raw('strftime("%Y-%m", created_at) as month'),
            DB::raw('count(*) as total')
        )
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->take(6)
        ->get()
        ->reverse();

        
        $requestsByStatus = ServiceRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return view('admin.reports.index', compact(
            'totalOffices', 'totalUsers', 'totalCitizens', 'totalStaff',
            'totalRequests', 'totalRevenue',
            'requestsPerOffice', 'requestsPerService',
            'monthlyRequests', 'requestsByStatus'
        ));
    }
}