<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';
use App\Models\{User, GalleryImage};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
class GalleryCompressionTest extends TestCase
{
    use RefreshDatabase;
    private string $imageRoot;
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url'=>'http://localhost']); app('url')->forceRootUrl('http://localhost');
        $this->imageRoot=storage_path('framework/testing/gallery-compression-'.bin2hex(random_bytes(5)));
        $this->app->usePublicPath($this->imageRoot);
        File::ensureDirectoryExists($this->imageRoot);
        $this->actingAs(User::create(['name'=>'Admin','email'=>'compression@example.test','password'=>'password','is_admin'=>true]));
    }
    protected function tearDown(): void
    {
        File::deleteDirectory($this->imageRoot);
        parent::tearDown();
    }
    public function test_gallery_upload_and_compression_end_to_end(): void
    {
        $source=base_path('public/images/home-safari/lion.jpg');
        $file=new UploadedFile($source,'lion.jpg','image/jpeg',null,true);
        $this->postJson('/admin/gallery',['images'=>[$file],'category'=>'__new__','new_category'=>'Audit photos','quality'=>95])
            ->assertOk()->assertJsonPath('success',true)->assertJsonPath('uploaded',1);
        $photo=GalleryImage::firstOrFail();
        $this->assertSame('Audit photos',$photo->category);
        $this->assertSame('image/webp',getimagesize(public_path($photo->path))['mime']);
        $hash=hash_file('sha256',public_path($photo->source_path));
        $this->assertSame(hash_file('sha256',$source),$hash);
        $before=$photo->optimized_size;
        $this->post('/admin/gallery/'.$photo->id.'/compress',['mode'=>'quality','quality_value'=>35])->assertSessionHasNoErrors();
        $photo->refresh();
        clearstatcache(true,public_path($photo->path));
        $this->assertLessThan($before,$photo->optimized_size);
        $this->assertEquals(filesize(public_path($photo->path)),$photo->optimized_size);
        $this->post('/admin/gallery/'.$photo->id.'/compress',['mode'=>'quality','quality_value'=>95])
            ->assertSessionHas('success',fn($message)=>str_contains($message,'already smaller') && !str_contains($message,'KB saved'));
        $this->post('/admin/gallery/'.$photo->id.'/compress',['mode'=>'filesize','filesize_value'=>10])
            ->assertSessionHas('success',fn($message)=>str_contains($message,'target could not be reached'));
        $this->assertSame($hash,hash_file('sha256',public_path($photo->source_path)));
        $this->get('/admin/gallery')->assertOk()->assertSee('Audit photos');
        $this->postJson('/admin/gallery',['images'=>[UploadedFile::fake()->create('bad.jpg',2,'text/plain')],'category'=>'Other'])->assertUnprocessable();
        $this->assertSame(1,GalleryImage::count());
    }
}
