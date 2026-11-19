<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Models\TourCategory;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(Request $request): View
    {
        $query = TourPackage::with(['destination', 'categories', 'accommodations'])
            ->publiclyVisible()->latest('published_at')->orderByDesc('id');
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim()->limit(100, '').'%';
            $query->where(fn ($search) => $search->where('title', 'like', $term)
                ->orWhereHas('destination', fn ($place) => $place->where('name', 'like', $term)->orWhere('country', 'like', $term)));
        }
        if ($request->filled('country')) {
            $countryName = Country::where('slug', (string) $request->string('country'))->where('is_active', true)->value('name');
            $query->whereHas('destination', fn ($destinationQuery) => $destinationQuery->where('country', $countryName ?? '__no_matching_country__'));
        }
        if ($request->filled('destination')) $query->where('destination_id', $request->integer('destination'));
        if ($request->filled('type')) $query->where('tour_type', (string) $request->string('type'));
        if ($request->integer('max_days') > 0) $query->where('duration_days', '<=', $request->integer('max_days'));
        if ($request->integer('min_days') > 0) $query->where('duration_days', '>=', $request->integer('min_days'));
        $lengths = TourPackage::publiclyVisible()->selectRaw('MIN(duration_days) as shortest, MAX(duration_days) as longest')->first();
        $durations = array_intersect(['2-5', '6-9', '10+'], (array) $request->query('duration', []));
        if ($durations) {
            $query->where(function ($range) use ($durations) {
                if (in_array('2-5', $durations)) $range->orWhereBetween('duration_days', [2, 5]);
                if (in_array('6-9', $durations)) $range->orWhereBetween('duration_days', [6, 9]);
                if (in_array('10+', $durations)) $range->orWhere('duration_days', '>=', 10);
            });
        }
        $priceBounds = TourPackage::publiclyVisible()->whereNotNull('price_from')
            ->selectRaw('currency, MIN(price_from) AS lowest, MAX(price_from) AS highest')
            ->groupBy('currency')->orderBy('currency')->get()->mapWithKeys(fn ($row) => [
                $row->currency => ['min' => floor($row->lowest), 'max' => ceil($row->highest)],
            ]);
        $currencies = $priceBounds->keys();
        $priceCurrency = (string) $request->string('price_currency');
        if (!$currencies->containsStrict($priceCurrency)) {
            $priceCurrency = $currencies->containsStrict('USD') ? 'USD' : $currencies->first();
        }
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minPrice = is_numeric($minPrice) && $minPrice >= 0 ? $minPrice : null;
        $maxPrice = is_numeric($maxPrice) && $maxPrice >= 0 ? $maxPrice : null;
        if ($priceCurrency && ($minPrice !== null || $maxPrice !== null)) {
            $query->where('currency', $priceCurrency)->whereNotNull('price_from');
            if ($minPrice !== null) $query->where('price_from', '>=', $minPrice);
            if ($maxPrice !== null) $query->where('price_from', '<=', $maxPrice);
        }
        if ($request->filled('category')) $query->whereHas('categories', fn ($q) => $q->where('tour_categories.slug', (string) $request->string('category')));
        if ($request->filled('comfort')) $query->whereHas('accommodations', fn ($q) => $q->where('tour_package_accommodations.tier', (string) $request->string('comfort')));

        $tours = $query->paginate(9)->withQueryString();
        abort_if($request->integer('page', 1) < 1 || $tours->currentPage() > $tours->lastPage(), 404);

        return view('tours.index', [
            'tours' => $tours,
            'currencies' => $currencies,
            'priceBounds' => $priceBounds,
            'priceCurrency' => $priceCurrency,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'durations' => $durations,
            'lengthBounds' => ['days' => ['min' => (int) ($lengths->shortest ?? 1), 'max' => (int) ($lengths->longest ?? 1)]],
            'destinations' => Destination::where('status', 'published')->orderBy('sort_order')->orderBy('name')->get(),
            'countries' => Country::where('is_active', true)->withCount(['tours as published_tours_count' => fn ($tourQuery) => $tourQuery->where('tour_packages.status', 'published')])->orderBy('sort_order')->orderBy('name')->get(),
            'categories' => TourCategory::where('is_active', true)->withCount(['packages' => fn ($q) => $q->where('status', 'published')])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function show(TourPackage $tour): View
    {
        abort_unless(TourPackage::publiclyVisible()->whereKey($tour->id)->exists(), 404);

        return $this->details($tour);
    }

    public function details(TourPackage $tour, bool $preview = false): View
    {
        $tour->loadMissing(['destination', 'categories', 'accommodations.destination']);
        $photoIds = $tour->accommodations->flatMap(fn ($stay) => $stay->gallery_photo_ids ?? [])->unique();

        return view('tours.show', [
            'tour' => $tour,
            'tourPreview' => $preview,
            'stayPhotos' => \App\Models\GalleryImage::whereIn('id', $photoIds)->pluck('path', 'id'),
            'itineraryDays' => \App\Support\SafariDays::sections($tour),
            'faqs' => !empty($tour->package_details['faqs'])
                ? collect($tour->package_details['faqs'])->map(fn ($faq) => (object) $faq)
                : \App\Models\Faq::where('is_active', true)->orderBy('sort_order')->take(6)->get(),
            'itineraryOptions' => TourPackage::whereIn('id', $tour->package_details['itinerary_option_ids'] ?? [])->publiclyVisible()->orderBy('duration_days')->get(),
            'related' => $this->relatedTours($tour),
        ]);
    }

    private function relatedTours(TourPackage $tour)
    {
        return TourPackage::with('destination')
            ->publiclyVisible()->where('id', '!=', $tour->id)
            ->when((int) $tour->duration_days === 1,
                fn ($query) => $query->where('duration_days', 1),
                fn ($query) => $query->where('duration_days', '>', 1))
            ->orderByRaw('CASE WHEN duration_days = ? THEN 0 ELSE 1 END', [(int) $tour->duration_days])
            ->when($tour->price_from !== null, fn ($query) => $query
                ->orderByRaw('CASE WHEN currency = ? AND price_from IS NOT NULL THEN 0 ELSE 1 END', [$tour->currency])
                ->orderByRaw('CASE WHEN currency = ? AND price_from IS NOT NULL THEN ABS(price_from - ?) END', [$tour->currency, (float) $tour->price_from]))
            ->orderByRaw('CASE WHEN duration_days >= ? THEN duration_days - ? ELSE ? - duration_days END', array_fill(0, 3, (int) $tour->duration_days))
            ->latest('published_at')->orderByDesc('id')->limit(3)->get();
    }
}
