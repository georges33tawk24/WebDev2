<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['category', 'office'])->latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $categories = Category::all();
        $offices    = Office::all();
        return view('admin.services.create', compact('categories', 'offices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id'                  => ['required', 'exists:offices,id'],
            'category_id'                => ['nullable', 'exists:categories,id'],
            'name'                       => ['required', 'string', 'max:255'],
            'name_ar'                    => ['nullable', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'description_ar'             => ['nullable', 'string'],
            'price'                      => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'required_documents'         => ['nullable', 'string'],
            'required_documents_ar'      => ['nullable', 'string'],
            'is_active'                  => ['boolean'],
        ]);

        $validated['required_documents'] = $request->filled('required_documents')
            ? array_filter(array_map('trim', explode(',', $request->required_documents)))
            : [];
        $validated['required_documents_ar'] = $request->filled('required_documents_ar')
            ? array_filter(array_map('trim', explode(',', $request->required_documents_ar)))
            : [];

        $validated['is_active'] = $request->boolean('is_active', true);

        Service::create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', __('ui.flash.service_created'));
    }

    public function edit(Service $service)
    {
        $categories = Category::all();
        $offices    = Office::all();
        return view('admin.services.edit', compact('service', 'categories', 'offices'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'office_id'                  => ['required', 'exists:offices,id'],
            'category_id'                => ['nullable', 'exists:categories,id'],
            'name'                       => ['required', 'string', 'max:255'],
            'name_ar'                    => ['nullable', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'description_ar'             => ['nullable', 'string'],
            'price'                      => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'required_documents'         => ['nullable', 'string'],
            'required_documents_ar'      => ['nullable', 'string'],
            'is_active'                  => ['boolean'],
        ]);

        $validated['required_documents'] = $request->filled('required_documents')
            ? array_filter(array_map('trim', explode(',', $request->required_documents)))
            : [];
        $validated['required_documents_ar'] = $request->filled('required_documents_ar')
            ? array_filter(array_map('trim', explode(',', $request->required_documents_ar)))
            : [];

        $validated['is_active'] = $request->boolean('is_active', false);

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', __('ui.flash.service_updated'));
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')
            ->with('success', __('ui.flash.service_deleted'));
    }
}