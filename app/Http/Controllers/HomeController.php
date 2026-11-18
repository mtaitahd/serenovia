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

        return view('accommodations.index', [
            'accommodations' => $query->paginate(9)->withQueryString(),
            'destinations' => Destination::where('status', 'published')->orderBy('name')->get(),
        ]);
    }

    public function __invoke(): View
    {
        $storyItems = json_decode(SiteSetting::get('our_story_gallery', ''), true);
        $storyItems = collect(is_array($storyItems) ? $storyItems : []);
        $storyImages = $storyItems->isEmpty()
            ? collect()
            : GalleryImage::whereIn('id', $storyItems->pluck('image_id'))->get()->keyBy('id')->pipe(fn ($images) => $storyItems->map(fn ($item) => $images->get($item['image_id']))->filter()->values());

        return view('home', [
            'destinations' => Destination::where('status', 'published')->orderByDesc('is_featured')->orderBy('sort_order')->take(4)->get(),
            'featuredTours' => TourPackage::with('destination')->where('status', 'published')->orderByDesc('is_featured')->latest('published_at')->take(3)->get(),
            'articles' => Article::where('status', 'published')->whereNotNull('published_at')->latest('published_at')->take(6)->get(),
            'featuredStays' => Accommodation::with('destination')->where('status', 'published')
                ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')->orderBy('id')->take(4)->get(),
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->take(6)->get(),
            'storyImages' => $storyImages,
        ]);
    }
}
