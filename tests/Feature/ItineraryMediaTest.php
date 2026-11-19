<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';

use App\Models\TourPackage;
use App\Models\User;
use App\Support\PublicImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItineraryMediaTest extends TestCase
{
    use RefreshDatabase;
    private array $photos = [];
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url'=>'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
        $this->app->usePublicPath(storage_path('framework/testing/itinerary-public'));
        File::ensureDirectoryExists(public_path());
        $this->actingAs(User::create(['name'=>'Test Admin','email'=>'itinerary-test@example.test','password'=>'test-password','is_admin'=>true]));
    }
    protected function tearDown(): void
    {
        foreach($this->photos as $path) PublicImage::delete($path);
        parent::tearDown();
    }
    private function payload(): array
    {
        return ['title'=>'Test Safari','slug'=>'test-safari','tour_type'=>'safari','duration_days'=>2,'duration_nights'=>1,'currency'=>'USD','status'=>'published','route_map_enabled'=>1,'itinerary_days'=>[
            ['id'=>(string)Str::uuid(),'title'=>'Arrival','details'=>'Meet the guide.','location'=>'First stop','latitude'=>null,'longitude'=>null],
            ['id'=>(string)Str::uuid(),'title'=>'Safari day','details'=>'Explore with the guide.','location'=>'Second stop','latitude'=>null,'longitude'=>null],
        ]];
    }
    private function createWithPhoto(): array
    {
        $data=$this->payload();$data['itinerary_days'][0]['uploads']=[UploadedFile::fake()->image('arrival.jpg',300,200)];
        $response=$this->post('/admin/tours',$data);$response->assertSessionHasNoErrors()->assertRedirect();
        $tour=TourPackage::firstOrFail();$this->photos=array_merge($this->photos,$tour->itinerary_days[0]['images']);
        unset($data['itinerary_days'][0]['uploads']);return [$tour,$data];
    }
    public function test_create_saves_day_upload_and_renders_map_and_day_gallery(): void
    {
        [$tour]=$this->createWithPhoto();
        $this->assertTrue($tour->route_map_enabled);$this->assertCount(2,$tour->itinerary_days);
        $this->assertFileExists(public_path($tour->itinerary_days[0]['images'][0]));
        $overview = $this->get('/safaris/'.$tour->slug)->assertOk()->assertSee('data-itinerary-map',false)->assertSee('itinerary-day-photos',false);
        $this->assertMatchesRegularExpression('/data-tour-panel="itinerary"\s+hidden/', $overview->getContent());
        $itinerary = $this->get('/safaris/'.$tour->slug.'?section=itinerary')->assertOk()->assertSee('itinerary-day-photos',false)->assertSee('First stop')->assertSee('id="rates"', false);
        $this->assertMatchesRegularExpression('/data-tour-panel="rates"\s+hidden/', $itinerary->getContent());
        $this->assertDoesNotMatchRegularExpression('/data-tour-panel="itinerary"\s+hidden/', $itinerary->getContent());
    }
    public function test_edit_preserves_photos_by_day_id_when_days_are_reordered(): void
    {
        [$tour,$data]=$this->createWithPhoto();$photo=$tour->itinerary_days[0]['images'][0];
        $data['itinerary_days']=array_reverse($data['itinerary_days']);
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame([$photo],$tour->fresh()->itinerary_days[1]['images']);
        $data['itinerary_days'][1]['remove_images']=[$photo];
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors();
        $this->assertSame([],$tour->fresh()->itinerary_days[1]['images']);
    }
    public function test_map_can_be_disabled_without_losing_day_photos(): void
    {
        [$tour,$data]=$this->createWithPhoto();$data['route_map_enabled']=0;
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors();
        $this->get('/safaris/'.$tour->slug)->assertOk()->assertDontSee('data-itinerary-map',false);
        $this->get('/safaris/'.$tour->slug.'?section=itinerary')->assertOk()->assertSee('itinerary-day-photos',false);
    }
    public function test_invalid_coordinates_and_non_image_uploads_are_rejected(): void
    {
        $data=$this->payload();$data['itinerary_days'][0]['latitude']=100;$data['itinerary_days'][0]['longitude']=30;
        $data['itinerary_days'][0]['uploads']=[UploadedFile::fake()->create('unsafe.txt',2,'text/plain')];
        $this->post('/admin/tours',$data)->assertSessionHasErrors(['itinerary_days.0.latitude','itinerary_days.0.uploads.0']);
        $this->assertDatabaseCount('tour_packages',0);
    }
    public function test_package_practical_details_can_be_saved_and_changed(): void
    {
        $data = $this->payload() + ['tour_code' => 'ser-test', 'max_group_size' => 6,
            'practical_details' => ['min_group_size' => 2, 'minimum_age' => 0, 'activity_level' => 'Easy', 'transport' => 'Private 4x4']];
        $this->post('/admin/tours', $data)->assertSessionHasNoErrors();
        $tour = TourPackage::firstOrFail();
        $this->assertSame('SER-TEST', $tour->tour_code);
        $this->get('/safaris/'.$tour->slug)->assertOk()->assertSee('Private 4x4')->assertSee('Minimum age');
        $data['practical_details']['min_group_size'] = 7;
        $this->put('/admin/tours/'.$tour->id, $data)->assertSessionHasErrors('practical_details.min_group_size');
        $data['practical_details']['min_group_size'] = 3;
        $data['practical_details']['transport'] = 'Shared vehicle';
        $this->put('/admin/tours/'.$tour->id, $data)->assertSessionHasNoErrors();
        $this->get('/safaris/'.$tour->slug)->assertOk()->assertSee('Shared vehicle')->assertDontSee('Private 4x4');
    }
    public function test_old_itineraries_remain_visible_and_both_admin_forms_have_controls(): void
    {
        $data=$this->payload();unset($data['itinerary_days']);$data['itinerary']="Day 1: Original arrival\nDay 2: Original safari";
        $this->post('/admin/tours',$data)->assertSessionHasNoErrors();$tour=TourPackage::firstOrFail();
        $this->get('/safaris/'.$tour->slug.'?section=itinerary')->assertOk()->assertSee('Original arrival');
        $this->get('/admin/tours/create')->assertOk()->assertSee('Need a route map?')->assertSee('data-day-field="uploads"',false);
        $this->get('/admin/tours/'.$tour->id.'/edit')->assertOk()->assertSee('data-initial-days',false)->assertSee('Need a route map?');
    }
}
