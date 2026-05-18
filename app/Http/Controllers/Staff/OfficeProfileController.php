<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfficeProfileController extends Controller
{
    public function edit()
    {
        $office = auth()->user()->office;

        if (!$office) {
            return redirect()->route('dashboard.staff')
                ->withErrors(['error' => __('ui.flash.no_office_assigned')]);
        }

        return view('staff.office.edit', compact('office'));
    }

    public function update(Request $request)
    {
        $office = auth()->user()->office;

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'name_ar'          => ['nullable', 'string', 'max:255'],
            'municipality'     => ['nullable', 'string', 'max:255'],
            'municipality_ar'  => ['nullable', 'string', 'max:255'],
            'address'          => ['nullable', 'string', 'max:255'],
            'address_ar'       => ['nullable', 'string', 'max:255'],
            'contact_number'   => ['nullable', 'string', 'max:50'],
            'contact_email'    => ['nullable', 'email', 'max:255'],
            'working_hours'    => ['nullable', 'string'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
        ]);

        if (array_key_exists('working_hours', $validated)) {
            $validated['working_hours'] = parse_working_hours_input($validated['working_hours']);
        }

        $office->update($validated);

        return redirect()->route('staff.office.edit')
            ->with('success', __('ui.flash.office_profile_updated'));
    }
}