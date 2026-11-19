<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';
use App\Models\{User, GalleryImage, Accommodation};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class GalleryStaySelectionTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
        $this->actingAs(User::create(['name'=>'Admin','email'=>'stay@example.test','password'=>'password','is_admin'=>true]));
    }
    public function test_custom_collection_is_saved_and_available_in_gallery(): void
    {
        $photo = GalleryImage::create(['path'=>'uploads/gallery/stay.webp','title'=>'Stay','category'=>'Other']);
        $this->put('/admin/gallery/'.$photo->id, ['category'=>'__new__','new_category'=>'Safari Camps'])->assertSessionHasNoErrors();
        $this->assertSame('Safari Camps', $photo->fresh()->category);
        $this->get('/admin/gallery')->assertOk()->assertSee('Safari Camps')->assertSee('Add new category');
        $this->put('/admin/gallery/'.$photo->id, ['category'=>'__new__','new_category'=>''])->assertSessionHasErrors('new_category');
    }
    public function test_stay_uses_gallery_cover_and_validates_selection(): void
    {
        $photos = collect(range(1,6))->map(fn($i)=>GalleryImage::create(['path'=>"uploads/gallery/stay-$i.webp",'title'=>"Stay $i",'category'=>'Accommodation']));
        $data=['name'=>'Safari Camp','slug'=>null,'tier'=>'mid_range','currency'=>'USD','status'=>'published','gallery_photo_ids'=>$photos->take(5)->pluck('id')->all()];
        $this->post('/admin/accommodations',$data)->assertSessionHasNoErrors();
        $stay=Accommodation::firstOrFail();
        $this->assertSame($photos->first()->path,$stay->image_path);
        $this->assertCount(5,$stay->gallery_photo_ids);
        $this->get('/admin/accommodations/'.$stay->id.'/edit')->assertOk()->assertSee('Select from gallery')->assertSee('data-photo-url',false);
        $data['gallery_photo_ids']=$photos->pluck('id')->all();
        $this->put('/admin/accommodations/'.$stay->id,$data)->assertSessionHasErrors('gallery_photo_ids');
        $data['gallery_photo_ids']=[999999];
        $this->put('/admin/accommodations/'.$stay->id,$data)->assertSessionHasErrors('gallery_photo_ids.0');
        $data['gallery_photo_ids']=[]; $data['gallery_edited']=1;
        $this->put('/admin/accommodations/'.$stay->id,$data)->assertSessionHasNoErrors();
        $this->assertNull($stay->fresh()->image_path);
        $this->assertSame([],$stay->fresh()->gallery_photo_ids);
        $this->assertSame(6,GalleryImage::count());
    }
}


