<?php
namespace Tests\Feature;
use App\Models\TourPackage;
use Tests\TestCase;
class DayPhotoLayoutTest extends TestCase
{
    public function test_day_photos_are_below_description_capped_at_five_and_transport_depends_on_type(): void
    {
        $tour=new TourPackage(['tour_type'=>'mountain']);
        $tour->setRelation('accommodations',collect());
        $day=['label'=>'Day 1','title'=>'Camp walk','html'=>'<p>Walking description</p>','images'=>['a.jpg','b.jpg','c.jpg','d.jpg','e.jpg','f.jpg'],'transport_images'=>['car.jpg']];
        $render=fn()=>view('public.tour-day-timeline',['tour'=>$tour,'itineraryDays'=>[$day],'stayPhotos'=>collect(),'tabUrl'=>fn($tab)=>'/'.$tab])->render();
        $html=$render();
        $this->assertStringNotContainsString('itinerary-day-collage',$html);
        $this->assertStringNotContainsString('f.jpg',$html);
        $this->assertStringNotContainsString('car.jpg',$html);
        $this->assertGreaterThan(strpos($html,'Walking description'),strpos($html,'itinerary-photo-row'));
        $this->assertSame(5,substr_count($html,'data-photo-group="itinerary-0"'));
        $tour->tour_type='safari';$this->assertStringContainsString('car.jpg',$render());
    }
    public function test_transport_sidebar_is_safari_only_and_has_true_count(): void
    {
        $tour=new TourPackage(['tour_type'=>'safari','duration_days'=>1]);$tour->setRelation('destination',null);$tour->setRelation('categories',collect());
        $days=[['label'=>'Day 1','title'=>'Drive','images'=>['a.jpg'],'transport_images'=>['car.jpg','car.jpg','bus.jpg']]];
        $render=fn()=>view('public.tour-sidebar-cards',['tour'=>$tour,'itineraryDays'=>$days,'tabUrl'=>fn($tab)=>'/'.$tab])->render();
        $this->assertStringContainsString('Open Transport Photos (2)',$render());
        $tour->tour_type='mountain';$this->assertStringNotContainsString('Open Transport Photos',$render());
    }
}
