<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SeoMetadata
{
    public static function url(string $path = ''): string
    {
        return rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
    }

    public static function text(?string $value, int $length = 170): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $length);
    }

    public static function page(array $data, $settings): array
    {
        $request = request();
        $path = $request->path();
        $canonical = self::url($path === '/' ? '' : $path);
        $filters = array_diff(array_keys($request->query()), ['page', 'section', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'fbclid']);
        $filtered = $request->routeIs('tours.index', 'accommodations.index') && count($filters) > 0;
        if ($filtered) {
            $query = $request->except(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'fbclid']);
            ksort($query);
            $canonical .= '?'.http_build_query($query);
        } elseif ($request->routeIs('tours.index', 'destinations.index', 'destinations.show', 'journal.index', 'accommodations.index') && $request->integer('page') > 1) {
            $canonical .= '?page='.$request->integer('page');
        }
        $description = match ($request->route()?->getName()) {
            'tours.index' => 'Explore Tanzania safari tours and itineraries. Compare destinations, trip lengths and accommodation, and request a quote for your dates.',
            'destinations.index' => 'Explore Tanzania destinations, from Serengeti and Ngorongoro to Kilimanjaro and Zanzibar, and find journeys to suit your plans.',
            'journal.index' => 'Read travel stories and practical inspiration for planning your Tanzania safari, mountain trek or island journey.',
            'contact' => 'Contact '.($settings['brand_name'] ?? 'Serenovia Africa Safaris').' to discuss your Tanzania itinerary, travel dates and group size.',
            default => $settings['hero_subtitle'] ?? 'Plan your Tanzania journey with Serenovia Africa Safaris.',
        };
        $image = $settings['hero_image'] ?? 'images/serenovia-logo.png';
        $type = 'website';
        if (isset($data['tour'])) {
            $description = $data['tour']->searchDescription();
            $image = SafariPhotography::tour($data['tour']);
        } elseif (isset($data['destination'])) {
            $description = $data['destination']->summary ?: $data['destination']->description;
            $image = $data['destination']->image_path ?: $image;
        } elseif (isset($data['article'])) {
            $description = $data['article']->excerpt ?: $data['article']->body;
            $image = $data['article']->image_path ?: $image;
            $type = 'article';
        }
        return [
            'canonical' => $canonical,
            'description' => self::text($description),
            'image' => preg_match('#^https?://#i', $image ?? '') ? $image : self::url($image ?: 'images/serenovia-logo.png'),
            'type' => $type,
            'robots' => $filtered || $request->routeIs('admin.*') ? 'noindex, follow' : 'index, follow, max-image-preview:large',
        ];
    }
}
