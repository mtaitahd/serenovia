<?php
namespace App\Support;

use App\Models\GalleryImage;
use App\Models\TourPackage;
use Illuminate\Support\Str;

class SafariDays
{
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
                'latitude' => isset($day['latitude']) ? (float) $day['latitude'] : null,
                'longitude' => isset($day['longitude']) ? (float) $day['longitude'] : null,
                'images' => array_values(array_unique($images)),
            ];
        })->all();
    }

    public static function sections(TourPackage $tour): array
    {
        if (!$tour->itinerary_days) return Itinerary::sections($tour->itinerary);
        return collect($tour->itinerary_days)->map(fn ($day, $index) => [
            'label' => 'Day '.($index + 1), 'title' => $day['title'] ?? '',
            'html' => RichText::display($day['details'] ?? ''),
            'location' => $day['location'] ?? '', 'latitude' => $day['latitude'] ?? null,
            'longitude' => $day['longitude'] ?? null, 'images' => $day['images'] ?? [],
        ])->all();
    }
}
