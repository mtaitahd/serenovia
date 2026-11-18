<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';
use App\Models\TourPackage;
use App\Models\User;
use App\Support\SafariDays;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
class CompleteTourPackageTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url'=>'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
        $this->actingAs(User::create(['name'=>'Admin','email'=>'package@example.test','password'=>'test-password','is_admin'=>true]));
    }
    private function payload(): array
    {
        return ['title'=>'Machame 6 days','tour_type'=>'mountain','duration_days'=>6,'duration_nights'=>5,'currency'=>'USD','status'=>'published',
            'package_details'=>['subtitle'=>'The Whiskey Route','meta_description'=>'Climb via Machame with Serenovia.', 'faqs'=>[['question'=>'What is the highest point?','answer'=>'Uhuru Peak at 5,895 metres.']]],
            'itinerary_days'=>[['id'=>(string)Str::uuid(),'title'=>'Machame Gate to Machame Camp','details'=>'Walk through the rainforest.','altitude'=>'1,640 m to 2,860 m','distance'=>'11 km','walking_time'=>'5 to 6 hours','habitat'=>'Rainforest','overnight_camp'=>'Machame Camp']]];
    }
    public function test_complete_trekking_fields_save_edit_and_render(): void
    {
        $data=$this->payload();
        $this->post('/admin/tours',$data)->assertSessionHasNoErrors()->assertRedirect();
        $tour=TourPackage::firstOrFail();
        $this->assertSame('Rainforest',$tour->itinerary_days[0]['habitat']);
        $this->assertSame('Machame Camp',SafariDays::sections($tour)[0]['overnight_camp']);
        $this->assertNull($tour->price_from);
        $this->get('/safaris/'.$tour->slug.'?section=itinerary')->assertOk()->assertSee('Rainforest')->assertSee('Machame Camp')->assertSee('The Whiskey Route')->assertSee('Uhuru Peak at 5,895 metres.')->assertSee('Climb via Machame with Serenovia.');
        $this->get('/admin/tours/'.$tour->id.'/edit')->assertOk()->assertSee('package_details[subtitle]',false)->assertSee('data-day-field="habitat"',false)->assertSee('PACKAGE QUESTIONS');
        $data['package_details']['faqs']=[];
        $data['itinerary_days'][0]['overnight_camp']='Shira Camp';
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors();
        $this->assertSame([],$tour->fresh()->package_details['faqs']);
        $this->assertSame('Shira Camp',$tour->fresh()->itinerary_days[0]['overnight_camp']);
    }
    public function test_options_are_validated_and_draft_options_stay_private(): void
    {
        $other=TourPackage::create(['title'=>'Machame 7 days','slug'=>'machame-seven','tour_type'=>'mountain','duration_days'=>7,'duration_nights'=>6,'currency'=>'USD','status'=>'draft']);
        $data=$this->payload();$data['package_details']['itinerary_option_ids']=[$other->id];
        $this->post('/admin/tours',$data)->assertSessionHasNoErrors();
        $tour=TourPackage::where('title','Machame 6 days')->firstOrFail();
        $this->get('/safaris/'.$tour->slug)->assertOk()->assertDontSee('Machame 7 days');
        $other->update(['status'=>'published']);
        $this->get('/safaris/'.$tour->slug)->assertOk()->assertSee('Machame 7 days');
        $data['package_details']['itinerary_option_ids']=[$tour->id];
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasErrors('package_details.itinerary_option_ids.0');
        $data['package_details']['itinerary_option_ids']=[999999];
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasErrors('package_details.itinerary_option_ids.0');
    }
    public function test_incomplete_faq_is_rejected_and_old_updates_preserve_extension(): void
    {
        $data=$this->payload();$data['package_details']['faqs'][0]['answer']='';
        $this->post('/admin/tours',$data)->assertSessionHasErrors('package_details.faqs.0.answer');
        $data=$this->payload();$this->post('/admin/tours',$data)->assertSessionHasNoErrors();
        $tour=TourPackage::firstOrFail();unset($data['package_details']);
        $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors();
        $this->assertSame('The Whiskey Route',$tour->fresh()->package_details['subtitle']);
    }
}

