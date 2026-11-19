<?php
namespace Tests\Unit;

use App\Support\{PublicImage, ResponsiveImage};
use Illuminate\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class PublicImageTest extends TestCase
{
    private string $root;
    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2).'/storage/framework/testing/image-'.bin2hex(random_bytes(6));
        $app = new Application(dirname(__DIR__, 2));
        $app->usePublicPath($this->root);
        $app->instance('files', new Filesystem());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        mkdir($this->root, 0777, true);
    }
    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->root);
        Facade::clearResolvedInstances();
        parent::tearDown();
    }
    private function photo(): UploadedFile
    {
        $source = dirname(__DIR__, 2).'/public/images/home-safari/lion.jpg';
        return new UploadedFile($source, 'lion.jpg', 'image/jpeg', null, true);
    }
    public function test_real_photo_becomes_smaller_valid_webp_and_original_is_unchanged(): void
    {
        $file = $this->photo();
        $path = PublicImage::store($file, 'audit');
        $info = getimagesize(public_path($path));
        $this->assertSame('image/webp', $info['mime']);
        $this->assertLessThanOrEqual(2400, max($info[0], $info[1]));
        $this->assertSame(hash_file('sha256', $file->getRealPath()), hash_file('sha256', public_path(PublicImage::sourceFor($path))));
        $this->assertLessThan(filesize($file->getRealPath()), filesize(public_path($path)));
        foreach (glob($this->root.'/uploads/audit/*w.webp') as $variant) {
            $this->assertSame('image/webp', getimagesize($variant)['mime']);
            $this->assertLessThan(filesize(public_path($path)), filesize($variant));
        }
        fwrite(STDOUT, "\nPhoto JPEG ".filesize($file->getRealPath())." bytes -> WebP ".filesize(public_path($path))." bytes\n");
    }
    public function test_recompression_reports_only_new_savings_and_never_increases_existing_webp(): void
    {
        $path = PublicImage::store($this->photo(), 'audit', 95);
        $before = filesize(public_path($path));
        $result = PublicImage::compress($path, null, 'quality', 35);
        $this->assertLessThan($before, $result['optimized_size']);
        $this->assertSame($before - $result['optimized_size'], $result['saved_bytes']);
        $this->assertSame(filesize(public_path($path)), $result['optimized_size']);
        $hash = hash_file('sha256', public_path($path));
        $again = PublicImage::compress($path, null, 'quality', 95);
        $this->assertSame(0, $again['saved_bytes']);
        $this->assertSame($hash, hash_file('sha256', public_path($path)));
        fwrite(STDOUT, "Compression ".$before." -> ".$result['optimized_size']." bytes; higher-quality retry retained smaller copy.\n");
    }
    public function test_png_transparency_and_resizing_are_preserved(): void
    {
        $image=imagecreatetruecolor(2600,100); imagealphablending($image,false); imagesavealpha($image,true);
        imagefill($image,0,0,imagecolorallocatealpha($image,0,0,0,127));
        imagefilledrectangle($image,100,20,200,80,imagecolorallocatealpha($image,200,30,60,0));
        imagepng($image,$this->root.'/transparent.png'); imagedestroy($image);
        $path=PublicImage::store(new UploadedFile($this->root.'/transparent.png','photo.png',null,null,true),'audit');
        $output=imagecreatefromwebp(public_path($path));
        $this->assertSame(2400, imagesx($output));
        $this->assertSame(127, imagecolorsforindex($output,imagecolorat($output,0,0))['alpha']);
        imagedestroy($output);
    }
    public function test_target_size_reports_when_target_cannot_be_met(): void
    {
        $path=PublicImage::store($this->photo(),'audit',95);
        $result=PublicImage::compress($path,null,'filesize',10);
        $this->assertSame($result['optimized_size']<=10240,$result['target_met']);
        $this->assertFalse($result['target_met']);
        $large=PublicImage::compress($path,null,'filesize',10240);
        $this->assertTrue($large['target_met']);
        $this->assertSame(0,$large['saved_bytes']);
    }
    public function test_client_filename_cannot_control_original_extension(): void
    {
        $path=PublicImage::store(new UploadedFile($this->photo()->getRealPath(),'photo.php',null,null,true),'audit');
        $this->assertStringEndsWith('.jpg',PublicImage::sourceFor($path));
    }
    public function test_copy_and_delete_keep_gallery_original_safe(): void
    {
        $path=PublicImage::store($this->photo(),'gallery');
        $copy=PublicImage::copy($path,'tours');
        PublicImage::delete($copy);
        $this->assertFileExists(public_path($path));
        $this->assertFileExists(public_path(PublicImage::sourceFor($path)));
        $this->assertSame([],glob($this->root.'/uploads/tours/*.webp'));
        $this->assertSame([],glob($this->root.'/uploads/tours/originals/*'));
    }
    public function test_phone_jpeg_orientation_is_applied(): void
    {
        $image=imagecreatetruecolor(120,80); ob_start(); imagejpeg($image); $jpeg=ob_get_clean(); imagedestroy($image);
        // EXIF TIFF little-endian IFD containing Orientation=6 (90 degrees clockwise).
        $exif="Exif\0\0II".pack('vVv',42,8,1).pack('vvVv',0x112,3,1,6)."\0\0".pack('V',0);
        file_put_contents($this->root.'/phone.jpg',substr($jpeg,0,2)."\xff\xe1".pack('n',strlen($exif)+2).$exif.substr($jpeg,2));
        $path=PublicImage::store(new UploadedFile($this->root.'/phone.jpg','phone.jpg',null,null,true),'audit');
        $size=getimagesize(public_path($path));
        $this->assertSame([80,120],[$size[0],$size[1]]);
    }
    public function test_optimized_webp_upload_is_not_made_larger(): void
    {
        $image=imagecreatefromjpeg($this->photo()->getRealPath());
        imagewebp($image,$this->root.'/small.webp',25); imagedestroy($image);
        $before=filesize($this->root.'/small.webp');
        $path=PublicImage::store(new UploadedFile($this->root.'/small.webp','small.webp',null,null,true),'audit');
        $this->assertLessThanOrEqual($before,filesize(public_path($path)));
        $this->assertSame('image/webp',getimagesize(public_path($path))['mime']);
    }
    public function test_small_images_are_not_upscaled_and_stale_variants_are_removed(): void
    {
        $image=imagecreatetruecolor(120,80); imagepng($image,$this->root.'/small.png'); imagedestroy($image);
        $path=PublicImage::store(new UploadedFile($this->root.'/small.png','small.png',null,null,true),'audit');
        $size=getimagesize(public_path($path));
        $this->assertSame([120,80],[$size[0],$size[1]]);
        $variant=substr(public_path($path),0,-5).'-480w.webp';
        file_put_contents($variant,'stale');
        ResponsiveImage::generate($path);
        $this->assertFileDoesNotExist($variant);
    }
    public function test_invalid_image_is_rejected_without_creating_uploads(): void
    {
        file_put_contents($this->root.'/bad.jpg','not an image');
        $this->expectException(\RuntimeException::class);
        PublicImage::store(new UploadedFile($this->root.'/bad.jpg','bad.jpg',null,null,true),'audit');
    }
}
