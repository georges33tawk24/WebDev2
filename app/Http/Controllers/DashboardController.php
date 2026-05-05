<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin');
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
