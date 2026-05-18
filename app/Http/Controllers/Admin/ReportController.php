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

        
        $monthlyRequests = ServiceRequest::query()
            ->get(['created_at'])
            ->groupBy(fn (ServiceRequest $request) => $request->created_at->format('Y-m'))
            ->map(fn ($group, string $month) => (object) [
                'month' => $month,
                'total' => $group->count(),
            ])
            ->sortKeysDesc()
            ->take(6)
            ->reverse()
            ->values();

        
        $requestsByStatus = ServiceRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $chartData = [
            'status' => [
                'labels' => $requestsByStatus
                    ->pluck('status')
                    ->map(fn (string $status) => __('ui.status.'.$status))
                    ->values()
                    ->all(),
                'data' => $requestsByStatus->pluck('total')->values()->all(),
            ],
            'monthly' => [
                'labels' => $monthlyRequests->pluck('month')->values()->all(),
                'data' => $monthlyRequests->pluck('total')->values()->all(),
                'datasetLabel' => __('ui.admin.total_requests'),
            ],
        ];

        return view('admin.reports.index', compact(
            'totalOffices', 'totalUsers', 'totalCitizens', 'totalStaff',
            'totalRequests', 'totalRevenue',
            'requestsPerOffice', 'requestsPerService',
            'monthlyRequests', 'requestsByStatus', 'chartData'
        ));
    }
}