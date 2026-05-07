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
        return view('dashboard.staff');
    }

    public function citizen(): View
    {
        return view('dashboard.citizen');
    }
} 