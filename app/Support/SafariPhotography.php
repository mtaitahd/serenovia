<?php

namespace App\Support;

use App\Models\TourPackage;

final class SafariPhotography
{
    public static function path(string $name): string
    {
        return 'images/home-safari/'.$name.'.jpg';
    }

    public static function destination(?string $slug): ?string
    {
        return match ($slug) {
            'lake-eyasi' => 'village',
            'tarangire-national-park', 'tarangire' => 'elephants',
            'serengeti', 'serengeti-national-park' => 'zebras',
            'ngorongoro', 'ngorongoro-crater' => 'crater',
            'kilimanjaro', 'mount-kilimanjaro' => 'kilimanjaro',
            'zanzibar' => 'zanzibar',
            default => null,
        };
    }

    public static function tour(TourPackage $tour): string
    {
        $photo = self::destination($tour->destination?->slug) ?? match ($tour->tour_type) {
            'mountain' => 'kilimanjaro',
            'island' => 'zanzibar',
            'cultural' => 'village',
            default => 'serengeti-leopard',
        };

        return self::path($photo);
    }

    public static function article(string $topic): string
    {
        $topic = strtolower($topic);

        return self::path(match (true) {
            str_contains($topic, 'kilimanjaro'), str_contains($topic, 'trekking') => 'kilimanjaro',
            str_contains($topic, 'zanzibar'), str_contains($topic, 'island') => 'zanzibar',
            str_contains($topic, 'migration') => 'migration',
            default => 'serengeti-sunset',
        });
    }
}
