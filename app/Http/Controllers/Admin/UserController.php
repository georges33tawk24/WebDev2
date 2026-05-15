<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'office'])->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function citizens()
    {
        $citizens = User::with('role')
            ->whereHas('role', fn ($q) => $q->where('slug', 'citizen'))
            ->latest()
            ->paginate(10);

        return view('admin.users.citizens', compact('citizens'));
    }

    public function createStaff(): View
    {
        $offices = Office::query()->orderBy('name')->get();

        return view('admin.users.create-staff', compact('offices'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'office_id' => ['required', 'exists:offices,id'],
        ]);

        $staffRole = Role::query()->firstOrCreate(
            ['slug' => 'office_staff'],
            ['name' => 'Office Staff']
        );

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $staffRole->id,
            'office_id' => $validated['office_id'],
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Office staff account created successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now(),
        ]);

        return back()->with('success', 'User status updated successfully!');
    }
}
