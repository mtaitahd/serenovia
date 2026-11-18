<?php

namespace App\Support;

use App\Models\{Accommodation, Article, Destination, Faq, SiteSetting, TourPackage};
use Illuminate\Support\Collection;

class HomepageContent
{
    public static function sections(): array
    {
        $sections = config('homepage');
        $photos = ['elephant' => 'An elephant walking on dry ground', 'zebras' => 'Zebras with mountains in the distance', 'kilimanjaro' => 'Snow-covered Mount Kilimanjaro', 'migration' => 'Wildlife across the plains', 'serengeti-leopard' => 'Leopard in a tree', 'beach' => 'A palm-lined beach'];
        $index = 1;
        foreach ($photos as $file => $caption) {
            $sections['social']['fields']['home_social_photo_'.$index] = ['label' => "Photograph {$index}", 'type' => 'image', 'default' => "images/home-safari/{$file}.jpg"];
            $sections['social']['fields']['home_social_caption_'.$index] = ['label' => "Photograph {$index} caption", 'type' => 'text', 'default' => $caption];
            $index++;
        }
        for ($i = 1; $i <= 3; $i++) {
            foreach (['title' => ['Title', 'text'], 'thumbnail' => ['Thumbnail', 'image'], 'url' => ['Video URL', 'url'], 'views' => ['Views text', 'text'], 'when' => ['Posted when', 'text']] as $key => [$label, $type]) {
                $sections['youtube']['fields']["home_video_{$i}_{$key}"] = ['label' => "Video {$i} · {$label}", 'type' => $type, 'default' => ''];
            }
        }
        foreach ($sections as $key => &$section) {
            if (! in_array($key, ['hero', 'branding', 'youtube'], true)) {
                $section['fields'] = ['home_'.$key.'_enabled' => ['label' => 'Show this section on the homepage', 'type' => 'toggle', 'default' => '1']] + $section['fields'];
            }
        }
        return $sections;
    }

    public static function values(?Collection $settings = null): array
    {
        $settings ??= collect(config('serenova.settings', []))->merge(SiteSetting::pluck('value', 'key'));
        $values = [];
        foreach (self::sections() as $section) {
            foreach ($section['fields'] as $key => $field) {
                $values[$key] = $settings->has($key) ? (string) $settings[$key] : $field['default'];
                if (in_array($key, ['hero_image', 'site_logo'], true) && $values[$key] === '') {
                    $values[$key] = $field['default'];
                }
            }
        }
        if (! $settings->has('home_social_title') && filled($settings['instagram'] ?? '')) {
            $values['home_social_title'] = 'Stay in touch on Instagram';
            $values['home_social_button'] = 'Follow on Instagram';
            $values['home_social_url'] = $settings['instagram'];
        }
        $legacyVideos = json_decode($settings['subscribe_videos'] ?? '[]', true) ?: [];
        foreach (array_slice($legacyVideos, 0, 3) as $index => $video) {
            foreach (['title', 'thumbnail', 'url', 'views', 'when'] as $key) {
                $field = 'home_video_'.($index + 1).'_'.$key;
                if (! $settings->has($field)) {
                    $values[$field] = $video[$key] ?? '';
                }
            }
        }
        return $values;
    }

    public static function query(string $kind)
    {
        return match ($kind) {
            'tours' => TourPackage::with('destination')->where('status', 'published')->orderByDesc('is_featured')->latest('published_at')->orderBy('id'),
            'accommodations' => Accommodation::with('destination')->where('status', 'published')->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')->orderBy('id'),
            'destinations' => Destination::where('status', 'published')->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('id'),
            'articles' => Article::where('status', 'published')->whereNotNull('published_at')->latest('published_at')->orderBy('id'),
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id'),
        };
    }

    public static function records(string $section, ?Collection $settings = null): Collection
    {
        $definition = self::sections()[$section];
        $stored = $settings ? $settings->get('home_'.$section.'_ids') : SiteSetting::get('home_'.$section.'_ids');
        $ids = json_decode($stored ?? 'null', true);
        $query = self::query($definition['collection']);
        if (is_array($ids)) {
            $records = $query->whereIn('id', $ids)->get()->keyBy('id');
            return collect($ids)->unique()->map(fn ($id) => $records->get($id))->filter()->take($definition['limit'])->values();
        }
        // The safari panel previously reused the three featured tours.
        return $query->take($section === 'safaris' ? 3 : $definition['limit'])->get();
    }

    public static function image($record): string
    {
        return $record->image_path ?: 'images/home-safari/serengeti-sunset.jpg';
    }

    public static function link(string $url): string
    {
        return str_starts_with($url, '/') ? url($url) : $url;
    }
}
