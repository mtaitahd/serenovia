<?php
namespace App\Support;
use App\Models\TourPackage;

final class MountainTrail
{
    /** Use this trail only for the standard Machame itinerary; never override custom pins. */
    public static function forTour(TourPackage $tour): ?array
    {
        $days = $tour->itinerary_days ?? [];
        if ($tour->tour_type !== 'mountain' || !str_contains(strtolower($tour->title), 'machame') || !in_array(count($days), [6,7], true)) return null;
        $data = json_decode(file_get_contents(base_path('public/js/data/machame-trail.json')), true, 512, JSON_THROW_ON_ERROR);
        $segments = $data['segments'];
        if (count($days) === 6) {
            $segments[3] = ['label'=>'Barafu Camp','coordinates'=>array_merge($segments[3]['coordinates'], array_slice($segments[4]['coordinates'],1))];
            array_splice($segments,4,1);
        }
        foreach ($days as $index => $day) {
            $expected = $segments[$index]['label'];
            $needle = $expected === 'Shira Camp' ? 'shira' : strtolower($expected);
            if (!str_contains(strtolower($day['title'] ?? ''), $needle)) return null;
            $end = $segments[$index]['coordinates'][array_key_last($segments[$index]['coordinates'])];
            if (isset($day['latitude'],$day['longitude']) && $day['latitude'] !== '' && $day['longitude'] !== '') {
                if (abs((float)$day['latitude']-$end[0]) > .009 || abs((float)$day['longitude']-$end[1]) > .009) return null;
            }
            if (!empty($day['location']) && !str_contains(strtolower($day['location']), $needle)) return null;
        }
        return $segments;
    }
}
