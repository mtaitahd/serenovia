<?php
namespace Tests\Feature;
use App\Models\{GalleryImage, TourPackage, User};
use App\Support\PublicImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
class PackagePersistenceTest extends TestCase
{
    use RefreshDatabase;
    public function test_cover_days_and_details_survive_reopening_and_resaving_draft_and_published_packages(): void
    {
        config(['app.url'=>'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
        $root = storage_path('framework/testing/package-persistence-'.Str::uuid());
        $this->app->usePublicPath($root);
        $this->actingAs(User::factory()->create(['is_admin'=>true]));
        try {
            $path = PublicImage::store(UploadedFile::fake()->image('photo.jpg',600,400),'gallery');
            $photo = GalleryImage::create(['path'=>$path,'title'=>'Test gallery photo','category'=>'Mountain']);
            foreach (['draft','published'] as $status) {
                $data=['title'=>'Persistence '.$status,'slug'=>'persistence-'.$status,'tour_type'=>'mountain','duration_days'=>1,'duration_nights'=>0,'currency'=>'USD','status'=>$status,'gallery_image_id'=>$photo->id,'description'=>'Saved overview','package_details'=>['subtitle'=>'Saved subtitle','faqs'=>[['question'=>'Saved question?','answer'=>'Saved answer']]],'itinerary_days'=>[['id'=>(string)Str::uuid(),'title'=>'Saved day','details'=>'Saved activities','altitude'=>'3000m','gallery_ids'=>[$photo->id],'transport_gallery_ids'=>[$photo->id]]]];
                $this->post('/admin/tours',$data)->assertSessionHasNoErrors()->assertRedirect();
                $tour=TourPackage::where('slug',$data['slug'])->sole();
                $cover=$tour->image_path;$days=$tour->itinerary_days;
                $this->assertFileExists(public_path($cover));
                $this->get('/admin/tours/'.$tour->id.'/edit')->assertOk()->assertSee($cover)->assertSee('Saved overview');
                unset($data['gallery_image_id'],$data['itinerary_days'][0]['gallery_ids']);
                $this->put('/admin/tours/'.$tour->id,$data)->assertSessionHasNoErrors()->assertRedirect();
                $tour->refresh();
                $this->assertSame($cover,$tour->image_path);
                $this->assertSame($days,$tour->itinerary_days);
                $this->assertSame($status,$tour->status);
                $this->assertSame('Saved answer',$tour->package_details['faqs'][0]['answer']);
            }
        } finally { File::deleteDirectory($root); }
    }
}
