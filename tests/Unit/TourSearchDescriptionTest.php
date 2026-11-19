<?php
namespace Tests\Unit;
use App\Models\TourPackage;
use PHPUnit\Framework\TestCase;
class TourSearchDescriptionTest extends TestCase
{
    public function test_description_falls_back_to_clean_overview_without_requiring_duplicate_fields(): void
    {
        $tour = new TourPackage(['title' => 'Machame 7 days', 'description' => '<p>Walk through rainforest.</p><p>Climb &amp; explore Kilimanjaro.</p>']);
        $this->assertSame('Walk through rainforest. Climb & explore Kilimanjaro.', $tour->searchDescription());
        $tour->summary = 'A shorter route introduction.';
        $this->assertSame('A shorter route introduction.', $tour->searchDescription());
        $tour->package_details = ['meta_description' => 'Custom Machame search description.'];
        $this->assertSame('Custom Machame search description.', $tour->searchDescription());
    }
    public function test_empty_rich_text_uses_title_and_long_automatic_text_is_shortened(): void
    {
        $tour = new TourPackage(['title' => 'Umbwe 7 days', 'description' => '<p>&nbsp;</p>']);
        $this->assertSame('Umbwe 7 days with Serenovia Africa Safaris.', $tour->searchDescription());
        $tour->description = str_repeat('Kilimanjaro trekking ', 40);
        $this->assertLessThanOrEqual(163, mb_strlen($tour->searchDescription()));
    }
}
