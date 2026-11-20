<?php
namespace Tests\Unit;
use App\Rules\GalleryImageDimensions;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
class GalleryImageDimensionsTest extends TestCase
{
    public function test_small_and_panoramic_images_are_accepted_but_oversized_images_have_a_clear_error(): void
    {
        foreach ([[80,60,true],[9000,100,true],[8000,6000,false]] as [$width,$height,$valid]) {
            $path=tempnam(sys_get_temp_dir(),'gallery-dim-');
            try {
                file_put_contents($path,"\x89PNG\r\n\x1a\n".pack('N',13).'IHDR'.pack('NNCCCCC',$width,$height,8,2,0,0,0).pack('N',0));
                $errors=[];
                (new GalleryImageDimensions())->validate('images.1',new UploadedFile($path,'photo.png',null,null,true),function($error) use (&$errors){$errors[]=$error;});
                if ($valid) $this->assertSame([],$errors);
                else { $this->assertCount(1,$errors); $this->assertStringContainsString('Photo 2',$errors[0]); $this->assertStringContainsString('40 megapixels',$errors[0]); }
            } finally { unlink($path); }
        }
    }
}
