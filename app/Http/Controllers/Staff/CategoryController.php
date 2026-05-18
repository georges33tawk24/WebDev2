<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('services')->latest()->paginate(10);

        return view('admin.categories.index', [
            'categories' => $categories,
            'catalogPrefix' => 'staff',
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['catalogPrefix' => 'staff']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
        ]);

        Category::create($validated);

        return redirect()
            ->route('staff.categories.index')
            ->with('success', __('ui.flash.category_created'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'catalogPrefix' => 'staff',
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return redirect()
            ->route('staff.categories.index')
            ->with('success', __('ui.flash.category_updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('staff.categories.index')
            ->with('success', __('ui.flash.category_deleted'));
    }
}
