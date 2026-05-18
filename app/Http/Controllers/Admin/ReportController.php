<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
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
        $totalCitizens = User::whereHas('role', fn ($query) => $query->where('slug', 'citizen'))->count();
        $totalStaff = User::whereHas('role', fn ($query) => $query->where('slug', 'office_staff'))->count();

        $totalRequests = ServiceRequest::count();
        $approvedRequests = ServiceRequest::where('status', 'approved')->count();
        $completedRequests = ServiceRequest::where('status', 'completed')->count();
        $rejectedRequests = ServiceRequest::where('status', 'rejected')->count();

        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $cardRevenue = Payment::where('status', 'paid')->where('method', 'card')->sum('amount');
        $cryptoRevenue = Payment::where('status', 'paid')->where('method', 'crypto')->sum('amount');

        $pendingCryptoPayments = Payment::where('method', 'crypto')
            ->where('status', 'pending')
            ->count();

        $averageRating = round(Feedback::avg('rating') ?? 0, 1);

        $requestsPerOffice = Office::withCount('serviceRequests')
            ->orderByDesc('service_requests_count')
            ->get();

        $requestsPerService = Service::withCount('serviceRequests')
            ->orderByDesc('service_requests_count')
            ->get();

        $monthlyRequests = ServiceRequest::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        
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

        $paymentsByMethod = Payment::select('method', DB::raw('count(*) as total'))
            ->groupBy('method')
            ->get();

        $cryptoByCurrency = Payment::where('method', 'crypto')
            ->select('crypto_currency', DB::raw('count(*) as total'))
            ->groupBy('crypto_currency')
            ->get();

        return view('admin.reports.index', compact(
            'totalOffices',
            'totalUsers',
            'totalCitizens',
            'totalStaff',
            'totalRequests',
            'approvedRequests',
            'completedRequests',
            'rejectedRequests',
            'totalRevenue',
            'cardRevenue',
            'cryptoRevenue',
            'pendingCryptoPayments',
            'averageRating',
            'requestsPerOffice',
            'requestsPerService',
            'monthlyRequests',
            'requestsByStatus',
            'paymentsByMethod',
            'cryptoByCurrency'
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