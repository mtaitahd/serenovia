<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\TourPackage;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function accommodations(\Illuminate\Http\Request $request): View
    {
        $query = Accommodation::with('destination')->where('status', 'published')->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name');
        if ($request->filled('tier')) $query->where('tier', (string) $request->string('tier'));
        if ($request->filled('destination')) $query->where('destination_id', $request->integer('destination'));
        if ($request->filled('q')) $query->where('name', 'like', '%'.(string) $request->string('q').'%');

        $hasStayFilters = collect(['q', 'destination', 'tier'])->contains(fn ($key) => $request->filled($key));
        $stayGallery = $hasStayFilters ? collect() : GalleryImage::orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn ($photo) => \App\Support\GalleryCategory::group($photo->category) === 'Accommodation');

        return view('accommodations.index', [
            'stayGallery' => $stayGallery,
            'hasStayFilters' => $hasStayFilters,
            'accommodations' => $query->paginate(9)->withQueryString(),
            'destinations' => Destination::where('status', 'published')->orderBy('name')->get(),
        ]);
    }

    public function __invoke(): View
    {
        $settings = collect(config('serenova.settings', []))->merge(SiteSetting::publicValues());
        $storyItems = collect(json_decode($settings->get('our_story_gallery', '[]'), true) ?: []);
        $photos = GalleryImage::whereIn('id', $storyItems->pluck('image_id'))->get()->keyBy('id');
        $storyImages = $storyItems->map(function ($item) use ($photos) {
            $image = $photos->get($item['image_id']);
            if ($image) $image->setAttribute('home_caption', $item['caption'] ?? '');
            return $image;
        })->filter()->values();
        return view('home', [
            'home' => \App\Support\HomepageContent::values($settings),
            'destinations' => \App\Support\HomepageContent::records('destinations', $settings),
            'featuredTours' => \App\Support\HomepageContent::records('journeys', $settings),
            'safariTours' => \App\Support\HomepageContent::records('safaris', $settings),
            'articles' => \App\Support\HomepageContent::records('journal', $settings),
            'featuredStays' => \App\Support\HomepageContent::records('stays', $settings),
            'faqs' => \App\Support\HomepageContent::records('faq', $settings),
            'storyImages' => $storyImages,
        ]);
    }
}
