<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TourCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.tour-categories.index', [
            'categories' => TourCategory::withCount('packages')->orderBy('sort_order')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.tour-categories.form', ['category' => new TourCategory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        TourCategory::create($data);

        return redirect()->route('admin.tour-categories.index')->with('success', 'Tour category added.');
    }

    public function edit(TourCategory $tourCategory): View
    {
        return view('admin.tour-categories.form', ['category' => $tourCategory]);
    }

    public function update(Request $request, TourCategory $tourCategory): RedirectResponse
    {
        $data = $this->validated($request, $tourCategory);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $tourCategory);
        $data['is_active'] = $request->boolean('is_active');
        $tourCategory->update($data);

        return redirect()->route('admin.tour-categories.index')->with('success', 'Tour category updated.');
    }

    public function destroy(TourCategory $tourCategory): RedirectResponse
    {
        $tourCategory->delete();
        return back()->with('success', 'Tour category removed.');
    }

    private function validated(Request $request, ?TourCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('tour_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $value, ?TourCategory $category = null): string
    {
        $base = Str::slug($value) ?: 'tour-category';
        $slug = $base;
        $n = 2;
        while (TourCategory::where('slug', $slug)->when($category, fn ($query) => $query->whereKeyNot($category->id))->exists()) {
            $slug = $base.'-'.$n++;
        }
        return $slug;
    }
}
