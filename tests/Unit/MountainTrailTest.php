<?php
namespace Tests\Unit;
use App\Models\TourPackage;
use App\Support\{MountainTrail, RouteStops};
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

class MountainTrailTest extends TestCase
{
    private function tours(): array
    {
        new Application(dirname(__DIR__, 2));
        return array_map(fn ($data) => new TourPackage($data), json_decode(file_get_contents(dirname(__DIR__,2).'/database/data/machame-route-2026-11-21.json'), true)['tours']);
    }
    public function test_imported_six_and_seven_day_treks_have_continuous_trails(): void
    {
        foreach ($this->tours() as $tour) {
            $trail=MountainTrail::forTour($tour);
            $this->assertCount($tour->duration_days,$trail);
            $this->assertEquals($trail[0]['coordinates'][0][0],RouteStops::start($tour)['latitude']);
            $days=array_map(fn ($d)=>$d+['label'=>'Day','images'=>[]],$tour->itinerary_days);
            $stops=RouteStops::forTour($tour,$days);
            foreach ($trail as $i=>$segment) {
                $this->assertGreaterThan(100,count($segment['coordinates']));
                $end=$segment['coordinates'][array_key_last($segment['coordinates'])];
                $this->assertEquals($end,[$stops[$i]['latitude'],$stops[$i]['longitude']]);
                if ($i) $this->assertEquals($trail[$i-1]['coordinates'][array_key_last($trail[$i-1]['coordinates'])],$segment['coordinates'][0]);
            }
            $this->assertCount(count($stops),array_unique(array_column($stops,'latitude')));
        }
    }
    public function test_custom_pins_are_not_overridden_and_transfer_text_is_not_a_pin(): void
    {
        $tour=$this->tours()[0];$days=$tour->itinerary_days;
        $days[0]['latitude']=-3.37;$days[0]['longitude']=36.63;$tour->itinerary_days=$days;
        $this->assertNull(MountainTrail::forTour($tour));
        $stops=RouteStops::forTour($tour,$days);
        $this->assertEquals(-3.37,$stops[0]['latitude']);
        $this->assertNull($stops[1]['latitude']);
        $tour->tour_type='safari';$this->assertNull(MountainTrail::forTour($tour));
    }
}
