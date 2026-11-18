<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\User;
use App\Support\TourGenerator\TourDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TourGeneratorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
    }

    public function test_an_admin_corrects_and_approves_a_document_then_generates_and_previews_distinct_drafts(): void
    {
        // Repeated saves in the same second must not depend on MySQL affected-row counts.
        $this->freezeTime();
        $this->get('/admin/package-generator')->assertRedirect();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->get('/admin/package-generator')->assertOk()->assertSee('Automatic Package Generator');
        $this->get(route('admin.generator.imports'))->assertOk()->assertSee('Upload for review')->assertDontSee('Generate package drafts');
        $this->get(route('admin.generator.index'))->assertOk()->assertSee('Generate package drafts')->assertDontSee('Upload for review');
        $destination = Destination::create(['name' => 'Test Park', 'slug' => 'test-park', 'country' => 'Tanzania', 'status' => 'published']);
        $csv = "day_title,tour_type,country_code,destination_id,start,end,description,activities\n";
        $csv .= "Bird walk,safari,TZ,9999,Test Park,Test Park,Observe endemic birds,Bird watching\n";
        $csv .= "Canopy expedition,safari,TZ,{$destination->id},Test Park,Test Park,Explore forest canopies,Canopy walk\n";
        $this->post('/admin/package-generator/imports', ['document' => UploadedFile::fake()->createWithContent('itinerary.csv', $csv)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $batch = DB::table('tour_gen_import_batches')->first();
        $record = DB::table('tour_gen_import_records')->where('status', 'invalid')->first();
        $this->assertNotNull($record);
        $this->get(route('admin.generator.review', $batch->id))->assertOk()->assertSee('Bird walk');
        $this->get(route('admin.generator.import-preview', [$batch->id, $record->id]))->assertOk()->assertSee('Public tour preview');
        $this->get(route('admin.generator.import-preview', ['batch' => $batch->id, 'record' => $record->id, 'section' => 'itinerary', 'frame' => 1]))
            ->assertOk()->assertSee('Bird walk');
        $this->post(route('admin.generator.approve', $batch->id))->assertSessionHasErrors('import');
        $this->assertDatabaseCount('tour_gen_day_blocks', 0);
        $fields = json_decode($record->mapped_payload, true);
        $fields['destination_id'] = (string) $destination->id;
        $vehicle = \App\Models\GalleryImage::create(['path' => 'uploads/gallery/test-vehicle.webp', 'category' => 'Transport']);
        $birds = \App\Models\GalleryImage::create(['path' => 'uploads/gallery/test-birds.webp', 'category' => 'Wildlife']);
        $this->patch(route('admin.generator.correct', [$batch->id, $record->id]), [
            'fields' => $fields, 'photo_picker' => 1, 'photo_ids' => [$birds->id], 'transport_ids' => [$vehicle->id], 'preview' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.generator.import-preview', [$batch->id, $record->id]));
        $this->get(route('admin.generator.import-preview', ['batch' => $batch->id, 'record' => $record->id, 'section' => 'itinerary', 'frame' => 1]))
            ->assertOk()->assertSeeInOrder(['itinerary-day-collage', 'test-birds.webp', 'Transport', 'test-vehicle.webp']);
        $this->post(route('admin.generator.approve', $batch->id))->assertSessionHasNoErrors();

        $filters = ['source' => 'hybrid', 'count' => 10, 'min_days' => 1, 'max_days' => 1, 'similarity' => 90];
        $response = $this->post(route('admin.generator.generate'), $filters)->assertSessionHasNoErrors()->assertRedirect();
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('2 of 10');
        $tour = TourPackage::firstOrFail();
        $this->assertSame('draft', $tour->status);
        $this->assertNull($tour->price_from);
        $this->get(route('admin.tours.edit', $tour))->assertOk()->assertSee('Bird walk');
        $this->get(route('admin.generator.preview', ['tour' => $tour]))->assertOk()->assertSee('Edit itinerary');
        $this->get(route('admin.generator.preview', ['tour' => $tour, 'section' => 'itinerary', 'frame' => 1]))
            ->assertOk()->assertSee('Bird walk')->assertSee('test-vehicle.webp');
        $this->get(route('tours.show', $tour->slug))->assertNotFound();
        $this->post(route('admin.generator.generate'), $filters)->assertRedirect();
        $this->assertDatabaseCount('tour_packages', 2);
    }

    public function test_office_templates_are_real_archives_and_can_be_read_after_completing_sample_rows(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('PHP zip is required for the Office round trip.');
        }
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['xlsx' => 'xl/worksheets/sheet1.xml', 'docx' => 'word/document.xml'] as $format => $member) {
            $response = $this->get(route('admin.generator.template', $format))->assertOk();
            $path = tempnam(sys_get_temp_dir(), 'serenovia-test-');
            try {
                file_put_contents($path, $response->getContent());
                $zip = new \ZipArchive;
                $this->assertTrue($zip->open($path));
                $data = $zip->getFromName($member);
                $this->assertIsString($data);
                $zip->addFromString($member, str_replace('EXAMPLE-01', 'COMPLETED-01', $data));
                $zip->close();
                $rows = app(TourDocuments::class)->read($path, $format);
                $this->assertSame('Forest walk', reset($rows)['day_title']);
            } finally {
                unlink($path);
            }
        }
    }

    public function test_bulk_run_creates_ten_distinct_drafts_from_approved_knowledge(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $import = app(\App\Support\TourGenerator\TourImport::class);
        for ($index = 1; $index <= 10; $index++) {
            $name = 'Fixture Park '.$index;
            $destination = Destination::create(['name' => $name, 'slug' => 'fixture-park-'.$index, 'country' => 'Tanzania', 'status' => 'published']);
            $import->createBlock([
                'day_title' => $name, 'tour_type' => 'safari', 'country_code' => 'TZ',
                'destination_id' => $destination->id, 'start' => $name, 'end' => $name,
                'description' => 'Observe wildlife in '.$name, 'activities' => 'Wildlife viewing',
            ], $admin->id);
        }
        $response = $this->post(route('admin.generator.generate'), [
            'source' => 'database', 'count' => 10, 'min_days' => 1, 'max_days' => 1, 'similarity' => 90,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('10 of 10');
        $this->assertSame(10, TourPackage::where('status', 'draft')->count());
        $this->assertDatabaseCount('tour_gen_run_results', 10);
    }
}
