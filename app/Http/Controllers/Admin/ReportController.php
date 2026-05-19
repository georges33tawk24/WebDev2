<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalOffices = Office::count();
        $totalUsers = User::count();
        $totalCitizens = User::whereHas('role', fn ($q) => $q->where('slug', 'citizen'))->count();
        $totalStaff = User::whereHas('role', fn ($q) => $q->where('slug', 'office_staff'))->count();
        $totalRequests = ServiceRequest::count();
        $totalRevenue = (float) Payment::query()
            ->where('status', 'paid')
            ->sum('amount');

        $requestsPerOffice = Office::withCount('serviceRequests')->get();

        $revenuePerOffice = Office::query()
            ->select('offices.*')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as paid_revenue')
            ->leftJoin('service_requests', 'service_requests.office_id', '=', 'offices.id')
            ->leftJoin('payments', function ($join): void {
                $join->on('payments.service_request_id', '=', 'service_requests.id')
                    ->where('payments.status', '=', 'paid');
            })
            ->groupBy('offices.id')
            ->orderByDesc('paid_revenue')
            ->get();

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
            'requestsPerOffice', 'requestsPerService', 'revenuePerOffice',
            'monthlyRequests', 'requestsByStatus', 'chartData'
        ));
    }
}
