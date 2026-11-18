<?php
namespace App\Support;

use App\Models\GalleryImage;
use App\Models\TourPackage;
use Illuminate\Support\Str;

class SafariDays
{
    public const TRAIL_FIELDS = [
        'altitude' => 'Elevation / altitude',
        'habitat' => 'Habitat',
        'distance' => 'Distance',
        'walking_time' => 'Walking Time',
    ];

    public static function prepare(array $days, ?TourPackage $tour = null): array
    {
        $saved = collect($tour?->itinerary_days ?? [])->keyBy('id');
        return collect(array_values($days))->map(function ($day) use ($saved) {
            $id = $day['id'] ?? (string) Str::uuid();
            $existing = $saved->get($id, []);
            $images = array_values(array_diff($existing['images'] ?? [], $day['remove_images'] ?? []));
            foreach ($day['uploads'] ?? [] as $file) $images[] = PublicImage::store($file, 'itinerary');
            foreach (array_unique($day['gallery_ids'] ?? []) as $galleryId) {
                $images[] = PublicImage::copy(GalleryImage::findOrFail($galleryId)->path, 'itinerary');
            }
            return [
                'id' => $id, 'title' => trim($day['title'] ?? ''), 'details' => trim($day['details'] ?? ''),
                'location' => trim($day['location'] ?? ''),
                'meals' => trim($day['meals'] ?? ''),
                'altitude' => trim($day['altitude'] ?? ''),
                'habitat' => trim($day['habitat'] ?? ''),
                'overnight_camp' => trim($day['overnight_camp'] ?? ''),
                'distance' => trim($day['distance'] ?? ''),
                'walking_time' => trim($day['walking_time'] ?? ''),
                'latitude' => isset($day['latitude']) ? (float) $day['latitude'] : null,
                'longitude' => isset($day['longitude']) ? (float) $day['longitude'] : null,
                'images' => array_values(array_unique($images)),
                'transport_gallery_ids' => array_slice(array_values(array_unique($day['transport_gallery_ids'] ?? [])), 0, 5),
            ];
        })->all();
    }

    public static function sections(TourPackage $tour): array
    {
        if (!$tour->itinerary_days) return Itinerary::sections($tour->itinerary);
        $ids = collect($tour->itinerary_days)->flatMap(fn ($day) => $day['transport_gallery_ids'] ?? [])->unique();
        $transport = GalleryImage::whereIn('id', $ids)->pluck('path', 'id');
        return collect($tour->itinerary_days)->map(fn ($day, $index) => [
            'label' => 'Day '.($index + 1), 'title' => $day['title'] ?? '',
            'html' => RichText::display($day['details'] ?? ''),
            'meals' => $day['meals'] ?? '',
            'altitude' => $day['altitude'] ?? '',
            'habitat' => $day['habitat'] ?? '',
            'overnight_camp' => $day['overnight_camp'] ?? '',
            'distance' => $day['distance'] ?? '',
            'walking_time' => $day['walking_time'] ?? '',
            'transport_images' => collect($day['transport_gallery_ids'] ?? [])->map(fn ($id) => $transport->get($id))->filter()->take(5)->values()->all(),
            'location' => $day['location'] ?? '', 'latitude' => $day['latitude'] ?? null,
            'longitude' => $day['longitude'] ?? null, 'images' => $day['images'] ?? [],
        ])->all();
    }
}
