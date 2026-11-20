<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachameDocumentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_packages_are_complete_drafts_without_invented_images_or_prices(): void
    {
        $document = json_decode(file_get_contents(database_path('data/machame-route-2026-11-21.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($document['tours'] as $source) {
            $tour = TourPackage::where('slug', $source['slug'])->sole();
            $this->assertSame('draft', $tour->status);
            $this->assertNull($tour->image_path);
            $this->assertNull($tour->price_from);
            $this->assertSame($source['description'], $tour->description);
            $this->assertSame($source['inclusions'], $tour->inclusions);
            $this->assertSame($source['exclusions'], $tour->exclusions);
            $this->assertSame($source['itinerary_days'], $tour->itinerary_days);
            $this->assertCount($source['duration_days'], $tour->itinerary_days);
            $this->assertSame($source['package_details']['faqs'], $tour->package_details['faqs']);
            $this->assertCount(10, $tour->package_details['faqs']);
            $other = TourPackage::findOrFail($tour->package_details['itinerary_option_ids'][0]);
            $this->assertNotSame($tour->duration_days, $other->duration_days);
        }
    }

    public function test_repeat_import_and_rollback_preserve_admin_changes(): void
    {
        $tour = TourPackage::where('slug', 'machame-route-6-day-itinerary')->sole();
        $tour->update(['summary' => 'Administrator edited this draft.', 'image_path' => 'uploads/admin-photo.webp']);
        $migration = require database_path('migrations/2026_11_21_120000_import_machame_document_packages.php');
        $migration->up();
        $migration->down();
        $tour->refresh();
        $this->assertSame('Administrator edited this draft.', $tour->summary);
        $this->assertSame('uploads/admin-photo.webp', $tour->image_path);
        $this->assertSame(2, TourPackage::whereIn('slug', ['machame-route-6-day-itinerary', 'machame-route-7-day-itinerary'])->count());
    }
}
