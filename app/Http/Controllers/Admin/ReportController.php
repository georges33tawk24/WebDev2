<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $totalOffices  = Office::count();
        $totalUsers    = User::count();
        $totalCitizens = User::whereHas('role', fn($q) => $q->where('slug', 'citizen'))->count();
        $totalStaff    = User::whereHas('role', fn($q) => $q->where('slug', 'office_staff'))->count();
        $offices       = Office::latest()->get();

        return view('admin.reports.index', compact(
            'totalOffices', 'totalUsers', 'totalCitizens', 'totalStaff', 'offices'
        ));
    }
}