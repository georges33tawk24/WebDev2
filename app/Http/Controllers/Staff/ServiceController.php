<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $officeId = $this->staffOfficeId();

        $services = Service::with(['category', 'office'])
            ->where('office_id', $officeId)
            ->latest()
            ->paginate(10);

        return view('admin.services.index', [
            'services' => $services,
            'catalogPrefix' => 'staff',
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'categories' => Category::orderBy('name')->get(),
            'offices' => collect([auth()->user()->office]),
            'catalogPrefix' => 'staff',
            'lockOffice' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $officeId = $this->staffOfficeId();

        $validated = $this->validatedServiceData($request);
        $validated['office_id'] = $officeId;
        $validated['is_active'] = $request->boolean('is_active', true);

        Service::create($validated);

        return redirect()
            ->route('staff.services.index')
            ->with('success', __('ui.flash.service_created'));
    }

    public function edit(Service $service): View
    {
        $this->authorizeOfficeService($service);

        return view('admin.services.edit', [
            'service' => $service,
            'categories' => Category::orderBy('name')->get(),
            'offices' => collect([auth()->user()->office]),
            'catalogPrefix' => 'staff',
            'lockOffice' => true,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorizeOfficeService($service);

        $validated = $this->validatedServiceData($request);
        $validated['office_id'] = $this->staffOfficeId();
        $validated['is_active'] = $request->boolean('is_active', false);

        $service->update($validated);

        return redirect()
            ->route('staff.services.index')
            ->with('success', __('ui.flash.service_updated'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorizeOfficeService($service);

        $service->delete();

        return redirect()
            ->route('staff.services.index')
            ->with('success', __('ui.flash.service_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedServiceData(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'required_documents' => ['nullable', 'string'],
            'required_documents_ar' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['required_documents'] = $request->filled('required_documents')
            ? array_filter(array_map('trim', explode(',', $request->required_documents)))
            : [];
        $validated['required_documents_ar'] = $request->filled('required_documents_ar')
            ? array_filter(array_map('trim', explode(',', $request->required_documents_ar)))
            : [];

        return $validated;
    }

    private function staffOfficeId(): int
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId, 403);

        return (int) $officeId;
    }

    private function authorizeOfficeService(Service $service): void
    {
        abort_unless(
            (int) $service->office_id === $this->staffOfficeId(),
            404
        );
    }
}
