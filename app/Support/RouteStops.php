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
        $destination=self::locate($tour->destination?->name ?? '');
        $previous=null;$stops=[];
        foreach($days as $index=>$day){
            $label=trim($day['location'] ?? '');
            $explicit=isset($day['latitude'],$day['longitude']);
            $place=$explicit ? ['label'=>$label ?: ($day['title'] ?: $day['label']),'latitude'=>(float)$day['latitude'],'longitude'=>(float)$day['longitude']] : self::locate($label);
            // A deliberately named, unknown stop must be pinned, never silently substituted.
            if(!$place && !$label)$place=self::locate(($day['title'] ?? '').' '.strip_tags($day['html'] ?? '')) ?? $destination ?? $previous;
            $stops[]=['day'=>$index+1,'label'=>$label ?: ($place['label'] ?? ($day['title'] ?: $day['label'])),'title'=>$day['title'] ?: $day['label'],'latitude'=>$place['latitude'] ?? null,'longitude'=>$place['longitude'] ?? null,'photo'=>!empty($day['images'][0])?asset($day['images'][0]):null];
            if($place)$previous=$place;
        }
        return $stops;
    }

    public static function start(TourPackage $tour): ?array { return self::locate($tour->starting_point ?? ''); }
}
