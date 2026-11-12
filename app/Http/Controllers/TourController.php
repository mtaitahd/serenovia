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
            ->where('status', 'published')->latest('published_at');
        if ($request->filled('q')) $query->where('title', 'like', '%'.(string) $request->string('q').'%');
        if ($request->filled('country')) {
            $countryName = Country::where('slug', (string) $request->string('country'))->where('is_active', true)->value('name');
            $query->whereHas('destination', fn ($destinationQuery) => $destinationQuery->where('country', $countryName ?? '__no_matching_country__'));
        }
        if ($request->filled('destination')) $query->where('destination_id', $request->integer('destination'));
        if ($request->filled('type')) $query->where('tour_type', (string) $request->string('type'));
        if ($request->filled('category')) $query->whereHas('categories', fn ($q) => $q->where('tour_categories.slug', (string) $request->string('category')));
        if ($request->filled('comfort')) $query->whereHas('accommodations', fn ($q) => $q->where('tour_package_accommodations.tier', (string) $request->string('comfort')));

        return view('tours.index', [
            'tours' => $query->paginate(9)->withQueryString(),
            'destinations' => Destination::where('status', 'published')->orderBy('sort_order')->orderBy('name')->get(),
            'countries' => Country::where('is_active', true)->withCount(['tours as published_tours_count' => fn ($tourQuery) => $tourQuery->where('tour_packages.status', 'published')])->orderBy('sort_order')->orderBy('name')->get(),
            'categories' => TourCategory::where('is_active', true)->withCount(['packages' => fn ($q) => $q->where('status', 'published')])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function show(TourPackage $tour): View
    {
        abort_unless($tour->status === 'published', 404);

        return view('tours.show', [
            'tour' => $tour->load(['destination', 'categories', 'accommodations.destination']),
            'itineraryDays' => \App\Support\SafariDays::sections($tour),
            'faqs' => \App\Models\Faq::where('is_active', true)->orderBy('sort_order')->take(6)->get(),
            'articles' => \App\Models\Article::where('status', 'published')->whereNotNull('published_at')->latest('published_at')->take(6)->get(),
            'related' => TourPackage::with(['destination', 'categories'])->where('status', 'published')->where('id', '!=', $tour->id)->where(function ($query) use ($tour) {
                if ($tour->categories->isNotEmpty()) $query->whereHas('categories', fn ($categoryQuery) => $categoryQuery->whereIn('tour_categories.id', $tour->categories->pluck('id')));
                else $query->where('destination_id', $tour->destination_id);
            })->take(3)->get(),
        ]);
    }
}
