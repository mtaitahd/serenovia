<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Destination;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(): View
    {
        return view('admin.destinations.index', ['destinations' => Destination::withCount('tours')->orderBy('sort_order')->paginate(15)]);
    }

    public function create(): View
    {
        return $this->form(new Destination());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        if ($image) $data['image_path'] = PublicImage::store($image, 'destinations');
        Destination::create($data);

        return redirect()->route('admin.destinations.index')->with('success', 'Destination created.');
    }

    public function edit(Destination $destination): View
    {
        return $this->form($destination);
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $data = $this->validated($request, $destination);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        if ($image) {
            PublicImage::delete($destination->image_path);
            $data['image_path'] = PublicImage::store($image, 'destinations');
        }
        $destination->update($data);

        return redirect()->route('admin.destinations.index')->with('success', 'Destination updated.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        PublicImage::delete($destination->image_path);
        $destination->delete();

        return back()->with('success', 'Destination deleted.');
    }

    private function validated(Request $request, ?Destination $destination = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('destinations', 'slug')->ignore($destination?->id)],
            'country' => ['required', 'string', 'max:120', Rule::exists('countries', 'name')],
            'tagline' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:12000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
    }

    private function form(Destination $destination): View
    {
        $countries = Country::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        if ($destination->country && ! $countries->contains('name', $destination->country)) {
            $countries->push(new Country(['name' => $destination->country, 'is_active' => false]));
        }

        return view('admin.destinations.form', compact('destination', 'countries'));
    }
}
