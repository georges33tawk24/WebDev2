<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        $totalOffices  = Office::count();
        $totalUsers    = User::count();
        $totalCitizens = User::whereHas('role', fn($q) => $q->where('slug', 'citizen'))->count();
        $totalStaff    = User::whereHas('role', fn($q) => $q->where('slug', 'office_staff'))->count();
        $recentOffices = Office::latest()->take(5)->get();
        $recentUsers   = User::with('role')->latest()->take(5)->get();

        return view('dashboard.admin', compact(
            'totalOffices', 'totalUsers', 'totalCitizens',
            'totalStaff', 'recentOffices', 'recentUsers'
        ));
    }

    public function staff(): View
{
    $user = auth()->user();
    $officeId = $user->office_id;

    $totalRequests   = \App\Models\ServiceRequest::where('office_id', $officeId)->count();
    $pendingRequests = \App\Models\ServiceRequest::where('office_id', $officeId)->where('status', 'pending')->count();
    $approvedRequests = \App\Models\ServiceRequest::where('office_id', $officeId)->where('status', 'approved')->count();
    $completedRequests = \App\Models\ServiceRequest::where('office_id', $officeId)->where('status', 'completed')->count();
    $recentRequests  = \App\Models\ServiceRequest::with(['citizen', 'service'])
        ->where('office_id', $officeId)
        ->latest()
        ->take(5)
        ->get();

    return view('dashboard.staff', compact(
        'totalRequests', 'pendingRequests', 'approvedRequests',
        'completedRequests', 'recentRequests'
    ));
}

    public function citizen(): View
    {
        return view('dashboard.citizen');
    }
} 