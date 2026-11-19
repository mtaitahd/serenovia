<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Accommodation::with('destination')->withCount('packages')->latest();
        if ($request->filled('tier')) $query->where('tier', (string) $request->string('tier'));
        if ($request->filled('status')) $query->where('status', (string) $request->string('status'));
        if ($request->filled('q')) $query->where('name', 'like', '%'.(string) $request->string('q').'%');
        return view('admin.accommodations.index', ['accommodations' => $query->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return $this->form(new Accommodation());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        unset($data['image'], $data['amenities_text']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['amenities'] = $this->amenities($request->input('amenities_text'));
        $data['is_featured'] = $request->boolean('is_featured');
        if ($image) $data['image_path'] = PublicImage::store($image, 'accommodations');
        if (!empty($data['gallery_photo_ids'])) $data['image_path'] = GalleryImage::findOrFail($data['gallery_photo_ids'][0])->path;
        Accommodation::create($data);
        return redirect()->route('admin.accommodations.index')->with('success', 'Accommodation added.');
    }

    public function edit(Accommodation $accommodation): View
    {
        return $this->form($accommodation);
    }

    public function update(Request $request, Accommodation $accommodation): RedirectResponse
    {
        $data = $this->validated($request, $accommodation);
        if ($request->boolean('gallery_edited')) $data['gallery_photo_ids'] = $data['gallery_photo_ids'] ?? [];
        $image = $data['image'] ?? null;
        unset($data['image'], $data['amenities_text']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $accommodation);
        $data['amenities'] = $this->amenities($request->input('amenities_text'));
        $data['is_featured'] = $request->boolean('is_featured');
        if ($image) {
            if (!GalleryImage::where('path', $accommodation->image_path)->exists()) PublicImage::delete($accommodation->image_path);
            $data['image_path'] = PublicImage::store($image, 'accommodations');
        }
        if (!empty($data['gallery_photo_ids'])) $data['image_path'] = GalleryImage::findOrFail($data['gallery_photo_ids'][0])->path;
        if ($request->boolean('gallery_edited') && empty($data['gallery_photo_ids']) && GalleryImage::where('path', $accommodation->image_path)->exists()) $data['image_path'] = null;
        $accommodation->update($data);
        return redirect()->route('admin.accommodations.index')->with('success', 'Accommodation updated.');
    }

    public function destroy(Accommodation $accommodation): RedirectResponse
    {
        if (!GalleryImage::where('path', $accommodation->image_path)->exists()) PublicImage::delete($accommodation->image_path);
        $accommodation->delete();
        return back()->with('success', 'Accommodation removed.');
    }

    private function form(Accommodation $accommodation): View
    {
        return view('admin.accommodations.form', [
            'accommodation' => $accommodation,
            'destinations' => Destination::orderBy('name')->get(),
            'galleryImages' => GalleryImage::latest()->get(),
        ]);
    }

    private function validated(Request $request, ?Accommodation $accommodation = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('accommodations', 'slug')->ignore($accommodation?->id)],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'tier' => ['required', Rule::in(['budget', 'mid_range', 'luxury'])],
            'location' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'amenities_text' => ['nullable', 'string', 'max:4000'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['required', 'string', 'size:3'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'gallery_photo_ids' => ['nullable', 'array', 'max:5'],
            'gallery_photo_ids.*' => ['integer', 'distinct', 'exists:gallery_images,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
    }

    private function amenities(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value ?? ''))->map(fn ($item) => trim($item))->filter()->unique()->values()->all();
    }

    private function uniqueSlug(string $value, ?Accommodation $accommodation = null): string
    {
        $base = Str::slug($value) ?: 'accommodation';
        $slug = $base;
        $n = 2;
        while (Accommodation::where('slug', $slug)->when($accommodation, fn ($query) => $query->whereKeyNot($accommodation->id))->exists()) {
            $slug = $base.'-'.$n++;
        }
        return $slug;
    }
}
