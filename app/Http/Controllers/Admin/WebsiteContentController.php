<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    public function heroImages(): View
    {
        return view('admin.website-content.hero-images', [
            'heroPages' => $this->heroPages(),
            'galleryImages' => GalleryImage::orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function updateHeroImages(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->heroPages() as $key => $label) {
            $rules['hero_uploads.'.$key] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'];
            $rules['hero_gallery.'.$key] = ['nullable', 'integer', 'exists:gallery_images,id'];
        }
        $data = $request->validate($rules);
        foreach ($this->heroPages() as $key => $label) {
            $imagePath = null;
            if ($request->hasFile('hero_uploads.'.$key)) {
                $imagePath = \App\Support\PublicImage::store($request->file('hero_uploads.'.$key), 'site/heroes');
            } elseif (!empty($data['hero_gallery'][$key])) {
                $imagePath = \App\Support\PublicImage::copy(GalleryImage::findOrFail($data['hero_gallery'][$key])->path, 'site/heroes');
            }
            if ($imagePath) SiteSetting::updateOrCreate(['key' => $key === 'home' ? 'hero_image' : $key.'_hero_image'], ['value' => $imagePath]);
        }
        return back()->with('success', 'Hero images saved.');
    }

    private function heroPages(): array
    {
        return ['home' => 'Homepage', 'safaris' => 'Safari journeys', 'destinations' => 'Destinations', 'accommodations' => 'Places to stay', 'journal' => 'Journal', 'gallery' => 'Gallery', 'contact' => 'Contact / Plan your trip'];
    }

    public function homepage(): View
    {
        return view('admin.website-content.homepage');
    }

    public function updateHomepage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'home_hero_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_primary_button' => ['required', 'string', 'max:100'],
            'home_secondary_button' => ['required', 'string', 'max:100'],
            'home_intro_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_intro_title' => ['required', 'string', 'max:255'],
            'home_intro_copy' => ['nullable', 'string', 'max:1500'],
            'home_journeys_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_journeys_title' => ['required', 'string', 'max:255'],
            'home_journeys_copy' => ['nullable', 'string', 'max:1000'],
            'home_destinations_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_destinations_title' => ['required', 'string', 'max:255'],
            'home_destinations_copy' => ['nullable', 'string', 'max:1000'],
            'home_journal_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_journal_title' => ['required', 'string', 'max:255'],
            'home_journal_copy' => ['nullable', 'string', 'max:1000'],
            'home_faq_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_faq_title' => ['required', 'string', 'max:255'],
            'home_faq_copy' => ['nullable', 'string', 'max:1000'],
            'home_plan_eyebrow' => ['nullable', 'string', 'max:120'],
            'home_plan_title' => ['required', 'string', 'max:255'],
            'home_plan_copy' => ['nullable', 'string', 'max:1000'],
            'home_plan_button' => ['required', 'string', 'max:100'],
        ]);
        $this->save($data);

        return back()->with('success', 'Homepage buttons and captions saved.');
    }

    public function travellers(): View
    {
        return view('admin.website-content.travellers');
    }

    public function updateTravellers(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'traveller_enabled' => ['nullable', 'boolean'],
            'traveller_badge' => ['nullable', 'string', 'max:120'],
            'traveller_title' => ['required', 'string', 'max:255'],
            'traveller_map_heading' => ['nullable', 'string', 'max:160'],
            'traveller_map_embed' => ['nullable', 'url', 'max:1000'],
            'traveller_tripadvisor_heading' => ['nullable', 'string', 'max:200'],
            'traveller_tripadvisor_text' => ['nullable', 'string', 'max:1000'],
            'traveller_tripadvisor_link' => ['nullable', 'url', 'max:1000'],
            'traveller_tripadvisor_button_text' => ['nullable', 'string', 'max:120'],
            'traveller_review_link' => ['nullable', 'url', 'max:1000'],
            'traveller_review_link_text' => ['nullable', 'string', 'max:120'],
        ]);
        $mapHost = strtolower((string) parse_url($data['traveller_map_embed'] ?? '', PHP_URL_HOST));
        $isGoogleMapHost = $mapHost === 'google.com' || str_ends_with($mapHost, '.google.com') || $mapHost === 'googleusercontent.com' || str_ends_with($mapHost, '.googleusercontent.com');
        if ($mapHost !== '' && ! $isGoogleMapHost) {
            return back()->withErrors(['traveller_map_embed' => 'Use a Google Maps embed URL.'])->withInput();
        }
        $data['traveller_enabled'] = $request->boolean('traveller_enabled') ? '1' : '0';
        $this->save($data);

        return back()->with('success', 'Traveller Stories section saved.');
    }

    public function subscribe(): View
    {
        return view('admin.website-content.subscribe');
    }

    public function updateSubscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subscribe_enabled' => ['nullable', 'boolean'],
            'subscribe_heading' => ['required', 'string', 'max:255'],
            'subscribe_youtube_url' => ['nullable', 'url', 'max:1000'],
            'subscribe_videos' => ['nullable', 'array', 'max:12'],
            'subscribe_videos.*.thumbnail' => ['nullable', 'url', 'max:1000'],
            'subscribe_videos.*.title' => ['nullable', 'string', 'max:255'],
            'subscribe_videos.*.views' => ['nullable', 'string', 'max:100'],
            'subscribe_videos.*.when' => ['nullable', 'string', 'max:100'],
            'subscribe_videos.*.url' => ['nullable', 'url', 'max:1000'],
        ]);
        $data['subscribe_enabled'] = $request->boolean('subscribe_enabled') ? '1' : '0';
        $videos = collect($data['subscribe_videos'] ?? [])->map(fn ($video) => collect($video)->map(fn ($value) => trim((string) $value))->all())
            ->filter(fn ($video) => ($video['title'] ?? '') !== '' || ($video['url'] ?? '') !== '' || ($video['thumbnail'] ?? '') !== '')
            ->take(3)->values();
        unset($data['subscribe_videos']);
        $this->save($data);
        SiteSetting::updateOrCreate(['key' => 'subscribe_videos'], ['value' => $videos->toJson()]);

        return back()->with('success', 'Subscribe section saved.');
    }

    public function footer(): View
    {
        $links = json_decode(SiteSetting::get('footer_explore_links', ''), true);
        $links = is_array($links) ? $links : [
            ['label' => 'Safari journeys', 'url' => '/safaris'],
            ['label' => 'Places to stay', 'url' => '/accommodations'],
            ['label' => 'Destinations', 'url' => '/destinations'],
            ['label' => 'Travel journal', 'url' => '/journal'],
            ['label' => 'Photo gallery', 'url' => '/gallery'],
        ];

        return view('admin.website-content.footer', ['footerLinksText' => collect($links)->map(fn ($link) => ($link['label'] ?? '').' | '.($link['url'] ?? ''))->implode("\n")]);
    }

    public function updateFooter(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'footer_tagline' => ['nullable', 'string', 'max:500'],
            'footer_location' => ['nullable', 'string', 'max:240'],
            'footer_explore_heading' => ['required', 'string', 'max:100'],
            'footer_contact_heading' => ['required', 'string', 'max:100'],
            'footer_links_text' => ['nullable', 'string', 'max:5000'],
            'footer_bottom_note' => ['nullable', 'string', 'max:180'],
        ]);
        $lines = collect(preg_split('/\r?\n/', $data['footer_links_text'] ?? '') ?: [])
            ->map(function ($line) {
                [$label, $url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                return ['label' => $label, 'url' => $url];
            })
            ->filter(fn ($link) => $link['label'] !== '' && $this->isSafeFooterUrl($link['url']))
            ->values();
        unset($data['footer_links_text']);
        $this->save($data + ['footer_explore_links' => $lines->toJson()]);

        return back()->with('success', 'Footer content saved.');
    }

    public function startEndPoints(): View
    {
        $starts = $this->settingList('starting_points', ['Moshi', 'Arusha', 'Kilimanjaro International Airport']);
        $ends = $this->settingList('ending_points', ['Moshi', 'Arusha', 'Zanzibar']);

        return view('admin.website-content.route-points', [
            'startingPointsText' => $starts->implode("\n"),
            'endingPointsText' => $ends->implode("\n"),
        ]);
    }

    public function updateStartEndPoints(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'starting_points' => ['nullable', 'array', 'max:100'],
            'starting_points.*' => ['nullable', 'string', 'max:255'],
            'ending_points' => ['nullable', 'array', 'max:100'],
            'ending_points.*' => ['nullable', 'string', 'max:255'],
        ]);
        foreach (['starting_points', 'ending_points'] as $key) {
            $values = collect($data[$key] ?? [])->map(fn ($value) => trim((string) $value))->filter()->unique()->values();
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $values->toJson()]);
        }

        return back()->with('success', 'Safari start and end point lists saved.');
    }

    public function packageDays(): View
    {
        return view('admin.website-content.package-days', [
            'packageDays' => $this->settingList('package_day_options', range(1, 10))
                ->map(fn ($day) => (int) $day)->filter(fn ($day) => $day > 0 && $day <= 365)->unique()->sort()->values(),
        ]);
    }

    public function updatePackageDays(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_days' => ['nullable', 'array', 'max:365'],
            'package_days.*' => ['required', 'integer', 'min:1', 'max:365', 'distinct'],
        ]);
        $days = collect($data['package_days'] ?? [])->map(fn ($day) => (int) $day)->unique()->sort()->values();
        SiteSetting::updateOrCreate(['key' => 'package_day_options'], ['value' => $days->toJson()]);

        return back()->with('success', 'Package day options saved.');
    }

    public function listingTitles(): View
    {
        return view('admin.website-content.listing-titles');
    }

    public function updateListingTitles(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'safaris_listing_title' => ['required', 'string', 'max:160'],
            'safaris_listing_eyebrow' => ['nullable', 'string', 'max:120'],
            'safaris_listing_intro' => ['nullable', 'string', 'max:1000'],
            'destinations_listing_title' => ['required', 'string', 'max:160'],
            'destinations_listing_eyebrow' => ['nullable', 'string', 'max:120'],
            'destinations_listing_intro' => ['nullable', 'string', 'max:1000'],
            'journal_listing_title' => ['required', 'string', 'max:160'],
            'journal_listing_eyebrow' => ['nullable', 'string', 'max:120'],
            'journal_listing_intro' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->save($data);

        return back()->with('success', 'Listing titles and introductions saved.');
    }

    public function storyImages(): View
    {
        $gallery = GalleryImage::orderBy('sort_order')->latest()->get();
        $items = json_decode(SiteSetting::get('our_story_gallery', ''), true);
        $items = is_array($items) ? $items : [];

        return view('admin.website-content.story-images', [
            'galleryImages' => $gallery,
            'storyItems' => collect($items)->keyBy('image_id'),
        ]);
    }

    public function updateStoryImages(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'our_story_title' => ['required', 'string', 'max:200'],
            'story_images' => ['nullable', 'array', 'max:12'],
            'story_images.*' => ['integer', 'exists:gallery_images,id'],
            'story_captions' => ['nullable', 'array'],
            'story_captions.*' => ['nullable', 'string', 'max:180'],
        ]);
        $items = collect($data['story_images'] ?? [])->unique()->values()->map(fn ($id) => [
            'image_id' => (int) $id,
            'caption' => trim((string) ($data['story_captions'][$id] ?? '')),
        ]);
        SiteSetting::updateOrCreate(['key' => 'our_story_title'], ['value' => $data['our_story_title']]);
        SiteSetting::updateOrCreate(['key' => 'our_story_gallery'], ['value' => $items->toJson()]);

        return back()->with('success', 'Our Story images and captions saved.');
    }

    private function save(array $values): void
    {
        foreach ($values as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (string) (int) $value : (string) ($value ?? '')]);
        }
    }

    private function isSafeFooterUrl(string $url): bool
    {
        return (str_starts_with($url, '/') && ! str_starts_with($url, '//')) || str_starts_with($url, '#')
            || preg_match('/^(https?:\/\/|mailto:|tel:)/i', $url) === 1;
    }

    private function settingList(string $key, array $fallback): Collection
    {
        $items = json_decode(SiteSetting::get($key, ''), true);
        return collect(is_array($items) ? $items : $fallback)->map(fn ($item) => trim((string) $item))->filter()->values();
    }
}
