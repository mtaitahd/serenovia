<?php
namespace App\Support;

use App\Models\TourPackage;

class RouteStops
{
    // Approximate place centres; explicit day pins always take priority.
    // Lake Eyasi: https://dahiti.dgfi.tum.de/en/13524/
    // Arusha: https://climate.icpac.net/downloads/RCC-METADATA.pdf
    private const PLACES = [
        'lake eyasi' => ['label'=>'Lake Eyasi','latitude'=>-3.6183,'longitude'=>35.0601],
        'arusha' => ['label'=>'Arusha','latitude'=>-3.37,'longitude'=>36.63],
    ];

    private static function locate(string $text): ?array
    {
        $text=strtolower(strip_tags($text));$match=null;$last=-1;
        foreach(self::PLACES as $name=>$point){$position=strrpos($text,$name);if($position!==false && $position>$last){$last=$position;$match=$point;}}
        return $match;
    }

    public static function forTour(TourPackage $tour, array $days): array
    {
        $trail=MountainTrail::forTour($tour);
        $stops=[];
        foreach($days as $index=>$day){
            $label=trim($day['location'] ?? '');
            $explicit=isset($day['latitude'],$day['longitude']) && is_numeric($day['latitude']) && is_numeric($day['longitude']);
            $place=$explicit ? ['label'=>$label ?: ($day['title'] ?: $day['label']),'latitude'=>(float)$day['latitude'],'longitude'=>(float)$day['longitude']] : self::locate($label);
            // A deliberately named, unknown stop must be pinned, never silently substituted.
            if ($trail) {
                $segment=$trail[$index];$end=$segment['coordinates'][array_key_last($segment['coordinates'])];
                $place=['label'=>$segment['label'],'latitude'=>$end[0],'longitude'=>$end[1]];
            } elseif (!$place && !$label) {
                // Descriptions mention transfers and past locations: never use them as day endpoints.
                $place=self::locate($day['title'] ?? '');
            }
            $stops[]=['day'=>$index+1,'label'=>$label ?: ($place['label'] ?? ($day['title'] ?: $day['label'])),'title'=>$day['title'] ?: $day['label'],'latitude'=>$place['latitude'] ?? null,'longitude'=>$place['longitude'] ?? null,'photo'=>!empty($day['images'][0])?asset($day['images'][0]):null];
        }
        return $stops;
    }

    public static function start(TourPackage $tour): ?array
    {
        $trail=MountainTrail::forTour($tour);
        if ($trail) { $point=$trail[0]['coordinates'][0];return ['label'=>'Machame Gate','latitude'=>$point[0],'longitude'=>$point[1]]; }
        return self::locate($tour->starting_point ?? '');
    }
}
