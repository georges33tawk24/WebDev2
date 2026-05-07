<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function citizens()
    {
        $citizens = User::with('role')
            ->whereHas('role', fn($q) => $q->where('slug', 'citizen'))
            ->latest()
            ->paginate(10);
        return view('admin.users.citizens', compact('citizens'));
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now(),
        ]);

        return back()->with('success', 'User status updated successfully!');
    }
}