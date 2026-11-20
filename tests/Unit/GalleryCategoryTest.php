<?php
namespace Tests\Unit;
use App\Support\GalleryCategory;
use PHPUnit\Framework\TestCase;
class GalleryCategoryTest extends TestCase
{
    public function test_custom_stay_names_and_spelling_variants_are_grouped(): void
    {
        foreach (['mountaic accomodation','Mountain Accommodation','ACCOMMODATIONS','Safari Lodges','mountain camp','Malazi','accomodations'] as $name) {
            $this->assertSame('Accommodation',GalleryCategory::group($name));
        }
        $this->assertSame('Transport',GalleryCategory::group('Safari vehicles'));
        $this->assertSame('Wildlife',GalleryCategory::group('Wildlife'));
        $this->assertSame('Mountain',GalleryCategory::group('Mountain'));
        $this->assertSame('Caracal',GalleryCategory::group('Caracal'));
    }
}
