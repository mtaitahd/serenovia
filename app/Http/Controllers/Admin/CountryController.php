<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::query()
            ->withCount('destinations')
            ->withCount(['tours as published_tours_count' => fn ($query) => $query->where('tour_packages.status', 'published')])
            ->orderBy('sort_order')->orderBy('name')->paginate(25);

        return view('admin.countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('admin.countries.form', ['country' => new Country()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        Country::create($data);

        return redirect()->route('admin.countries.index')->with('success', 'Country added to the tour filters.');
    }

    public function edit(Country $country): View
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $data = $this->validated($request, $country);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $previousName = $country->name;
        $country->update($data);
        if ($previousName !== $country->name) {
            Destination::where('country', $previousName)->update(['country' => $country->name]);
        }

        return redirect()->route('admin.countries.index')->with('success', 'Country details updated.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        if ($country->destinations()->exists()) {
            return back()->with('error', 'This country is assigned to destinations. Update those destinations before deleting it.');
        }

        $country->delete();
        return back()->with('success', 'Country removed.');
    }

    private function validated(Request $request, ?Country $country = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('countries', 'name')->ignore($country?->id)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('countries', 'slug')->ignore($country?->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
