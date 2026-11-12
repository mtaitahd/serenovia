<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Accommodation;
use App\Models\GalleryImage;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\SiteSetting;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TourPackageController extends Controller
{
    public function index(Request $request): View
    {
        $query = TourPackage::with(['destination', 'categories'])->latest();
        if ($request->filled('status')) $query->where('status', (string) $request->string('status'));
        if ($request->filled('destination')) $query->where('destination_id', $request->integer('destination'));
        if ($request->filled('category')) $query->whereHas('categories', fn ($q) => $q->where('tour_categories.id', $request->integer('category')));
        if ($request->filled('q')) $query->where('title', 'like', '%'.(string) $request->string('q').'%');
        return view('admin.tours.index', [
            'tours' => $query->paginate(12)->withQueryString(),
            'destinations' => Destination::orderBy('sort_order')->orderBy('name')->get(),
            'startingPoints' => $this->routePoints('starting_points', ['Moshi', 'Arusha', 'Kilimanjaro International Airport']),
            'endingPoints' => $this->routePoints('ending_points', ['Moshi', 'Arusha', 'Zanzibar']),
            'categories' => TourCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedPlan = $request->session()->getOldInput('accommodation_plan');
        if (! is_array($selectedPlan) || $selectedPlan === []) {
            $selectedPlan = [['day_number' => 1, 'tier' => 'mid_range', 'accommodation_id' => '']];
        }

        return view('admin.tours.form', [
            'tour' => new TourPackage(),
            'selectedPlan' => $selectedPlan,
            'dayOptions' => $this->packageDayOptions($selectedPlan),
            'destinations' => Destination::orderBy('sort_order')->orderBy('name')->get(),
            'startingPoints' => $this->routePoints('starting_points', ['Moshi', 'Arusha', 'Kilimanjaro International Airport']),
            'endingPoints' => $this->routePoints('ending_points', ['Moshi', 'Arusha', 'Zanzibar']),
            'galleryImages' => GalleryImage::latest()->get(),
            'categories' => TourCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'accommodations' => Accommodation::where('status', 'published')->with('destination')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        $galleryImageId = $data['gallery_image_id'] ?? null;
        unset($data['image'], $data['gallery_image_id'], $data['category_ids'], $data['accommodation_plan']);

        $data['slug'] = $this->slug($data['slug'] ?? '', $data['title']);
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? now()) : null;
        if ($image) {
            $data['image_path'] = PublicImage::store($image, 'tours');
        } elseif ($galleryImageId) {
            $galleryImage = GalleryImage::findOrFail($galleryImageId);
            $data['image_path'] = PublicImage::copy($galleryImage->path, 'tours');
        }
        $tour = TourPackage::create($data);
        $this->syncPackageOptions($request, $tour);

        return redirect()->route('admin.tours.index')->with('success', 'Safari package created.');
    }

    public function edit(Request $request, TourPackage $tour): View
    {
        $tour->load(['categories', 'accommodations']);
        $selectedPlan = $request->session()->getOldInput('accommodation_plan');
        if (! is_array($selectedPlan)) {
            $selectedPlan = $tour->accommodations->map(fn ($stay) => [
                'day_number' => $stay->pivot->day_number,
                'tier' => $stay->pivot->tier,
                'accommodation_id' => $stay->id,
            ])->all();
        }
        if ($selectedPlan === []) {
            $selectedPlan = [['day_number' => 1, 'tier' => 'mid_range', 'accommodation_id' => '']];
        }

        return view('admin.tours.form', [
            'tour' => $tour,
            'selectedPlan' => $selectedPlan,
            'dayOptions' => $this->packageDayOptions($selectedPlan),
            'destinations' => Destination::orderBy('sort_order')->orderBy('name')->get(),
            'startingPoints' => $this->routePoints('starting_points', ['Moshi', 'Arusha', 'Kilimanjaro International Airport'], $tour->starting_point),
            'endingPoints' => $this->routePoints('ending_points', ['Moshi', 'Arusha', 'Zanzibar'], $tour->ending_point),
            'galleryImages' => GalleryImage::latest()->get(),
            'categories' => TourCategory::where(fn ($query) => $query->where('is_active', true)->orWhereHas('packages', fn ($packages) => $packages->whereKey($tour->id)))->orderBy('sort_order')->orderBy('name')->get(),
            'accommodations' => Accommodation::where(fn ($query) => $query->where('status', 'published')->orWhereHas('packages', fn ($packages) => $packages->whereKey($tour->id)))->with('destination')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, TourPackage $tour): RedirectResponse
    {
        $data = $this->validated($request, $tour);
        $image = $data['image'] ?? null;
        $galleryImageId = $data['gallery_image_id'] ?? null;
        unset($data['image'], $data['gallery_image_id'], $data['category_ids'], $data['accommodation_plan']);

        $data['slug'] = $this->slug($data['slug'] ?? '', $data['title']);
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? $tour->published_at ?? now()) : null;
        if ($image) {
            PublicImage::delete($tour->image_path);
            $data['image_path'] = PublicImage::store($image, 'tours');
        } elseif ($galleryImageId) {
            $galleryImage = GalleryImage::findOrFail($galleryImageId);
            PublicImage::delete($tour->image_path);
            $data['image_path'] = PublicImage::copy($galleryImage->path, 'tours');
        }
        $tour->update($data);
        $this->syncPackageOptions($request, $tour);

        return redirect()->route('admin.tours.index')->with('success', 'Safari package updated.');
    }

    public function destroy(TourPackage $tour): RedirectResponse
    {
        PublicImage::delete($tour->image_path);
        $tour->delete();

        return back()->with('success', 'Safari package deleted.');
    }

    private function validated(Request $request, ?TourPackage $tour = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('tour_packages', 'slug')->ignore($tour?->id)],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'starting_point' => ['nullable', 'string', 'max:255'],
            'ending_point' => ['nullable', 'string', 'max:255'],
            'tour_type' => ['required', 'string', 'max:60'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_nights' => ['required', 'integer', 'min:0', 'max:365'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['required', 'string', 'size:3'],
            'max_group_size' => ['nullable', 'integer', 'min:1', 'max:500'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:30000'],
            'itinerary' => ['nullable', 'string', 'max:30000'],
            'route_map_enabled' => ['nullable', 'boolean'],
            'itinerary_days' => ['nullable', 'array', 'max:365'],
            'itinerary_days.*.id' => ['required', 'uuid', 'distinct'],
            'itinerary_days.*.title' => ['nullable', 'string', 'max:180'],
            'itinerary_days.*.details' => ['nullable', 'string', 'max:12000'],
            'itinerary_days.*.location' => ['nullable', 'string', 'max:180'],
            'itinerary_days.*.latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:itinerary_days.*.longitude'],
            'itinerary_days.*.longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:itinerary_days.*.latitude'],
            'itinerary_days.*.gallery_ids' => ['nullable', 'array', 'max:6'],
            'itinerary_days.*.gallery_ids.*' => ['integer', 'exists:gallery_images,id'],
            'itinerary_days.*.uploads' => ['nullable', 'array', 'max:6'],
            'itinerary_days.*.uploads.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'itinerary_days.*.remove_images' => ['nullable', 'array'],
            'itinerary_days.*.remove_images.*' => ['string', 'max:255'],
            'inclusions' => ['nullable', 'string', 'max:12000'],
            'exclusions' => ['nullable', 'string', 'max:12000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'gallery_image_id' => ['nullable', 'integer', 'exists:gallery_images,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:tour_categories,id'],
            'accommodation_plan' => ['nullable', 'array'],
            'accommodation_plan.*.day_number' => ['required', 'integer', 'min:1', 'max:365'],
            'accommodation_plan.*.tier' => ['required', Rule::in(['budget', 'mid_range', 'luxury'])],
            'accommodation_plan.*.accommodation_id' => ['nullable', 'integer', 'exists:accommodations,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
                ]) + ['is_featured' => $request->boolean('is_featured')];
        $data['route_map_enabled'] = $request->boolean('route_map_enabled');
        if (array_key_exists('itinerary_days', $data)) {
            if (count($data['itinerary_days'] ?? []) > (int)$data['duration_days']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['itinerary' => 'The number of itinerary days cannot exceed the safari duration.']);
            }
            $data['itinerary_days'] = \App\Support\SafariDays::prepare($data['itinerary_days'] ?? [], $tour);
        }
        return $data;
    }

    private function syncPackageOptions(Request $request, TourPackage $tour): void
    {
        $tour->categories()->sync($request->input('category_ids', []));
        $assignments = [];
        foreach ((array) $request->input('accommodation_plan', []) as $item) {
            $accommodationId = $item['accommodation_id'] ?? null;
            if (! $accommodationId) continue;
            $assignments[] = [
                'tour_package_id' => $tour->id,
                'accommodation_id' => (int) $accommodationId,
                'day_number' => (int) $item['day_number'],
                'tier' => $item['tier'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::transaction(function () use ($tour, $assignments): void {
            DB::table('tour_package_accommodations')->where('tour_package_id', $tour->id)->delete();
            if ($assignments !== []) DB::table('tour_package_accommodations')->insert($assignments);
        });
    }

    private function slug(string $slug, string $title): string
    {
        return Str::slug($slug !== '' ? $slug : $title);
    }

    private function routePoints(string $key, array $fallback, ?string $current = null): array
    {
        $points = json_decode(SiteSetting::get($key, ''), true);
        $points = is_array($points) ? $points : $fallback;
        if ($current && ! in_array($current, $points, true)) $points[] = $current;

        return array_values(array_unique(array_filter($points, fn ($point) => is_string($point) && trim($point) !== '')));
    }

    private function packageDayOptions(array $selectedPlan = []): array
    {
        $configured = json_decode(SiteSetting::get('package_day_options', ''), true);
        $configured = is_array($configured) ? $configured : range(1, 10);
        $savedSelections = collect($selectedPlan)->pluck('day_number')->filter()->all();

        return collect($configured)->merge($savedSelections)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 365)
            ->unique()->sort()->values()->all();
    }
}
