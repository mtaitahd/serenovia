<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';

use App\Models\{Article, Destination, TourPackage};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
    }

    private function tour(array $extra = []): TourPackage
    {
        return TourPackage::create(array_merge(['title' => 'Verified trek', 'slug' => 'verified-trek', 'tour_type' => 'mountain', 'duration_days' => 1, 'duration_nights' => 0, 'currency' => 'USD', 'status' => 'published', 'summary' => 'Walk with our local team.', 'description' => 'A forest walk.', 'itinerary' => 'Day 1: Unique forest itinerary description.', 'inclusions' => 'Guide and picnic.', 'exclusions' => 'Flights.', 'image_path' => 'images/test-cover.jpg'], $extra));
    }

    public function test_canonical_html_contains_all_sections_and_truthful_schema(): void
    {
        config(['app.url' => 'https://www.serenoviaafrica.com']);
        $tour = $this->tour();
        $response = $this->get('/safaris/'.$tour->slug)->assertOk()->assertSee('Unique forest itinerary description.')->assertSee('Guide and picnic.')->assertSee('Flights.')->assertSee('Price on request');
        $response->assertSee('https://www.serenoviaafrica.com/safaris/verified-trek', false)->assertSee('summary_large_image')->assertSee('images/test-cover.jpg');
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);
        $schema = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $trip = collect($schema['@graph'])->firstWhere('@type', 'TouristTrip');
        $this->assertSame('Verified trek', $trip['name']);
        $this->assertArrayNotHasKey('offers', $trip);
        $this->assertArrayNotHasKey('aggregateRating', $trip);
        $this->get('/safaris/'.$tour->slug.'?section=included')->assertOk()->assertSee('Unique forest itinerary description.');
    }

    public function test_discovery_updates_without_manual_generation_and_excludes_private_records(): void
    {
        config(['app.url' => 'https://www.serenoviaafrica.com']);
        $tour = $this->tour();
        $draft = $this->tour(['title' => 'Private', 'slug' => 'private-tour', 'status' => 'draft']);
        Article::create(['title' => 'Future story', 'slug' => 'future-story', 'category' => 'Travel', 'body' => 'Future', 'status' => 'published', 'published_at' => now()->addDay()]);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/safaris/verified-trek')->assertSee('/travel-guide')->assertSee('<lastmod>', false)->assertDontSee('private-tour')->assertDontSee('future-story')->assertDontSee('localhost');
        $this->get('/safaris/'.$draft->slug)->assertNotFound();
        $this->get('/journal/future-story')->assertNotFound();
        $tour->update(['status' => 'draft']);
        $this->get('/sitemap.xml')->assertDontSee('/safaris/verified-trek');
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://www.serenoviaafrica.com/sitemap.xml');
    }

    public function test_filter_pagination_and_admin_directives(): void
    {
        $this->tour();
        $this->get('/safaris?type=mountain')->assertOk()->assertSee('noindex, follow');
        $this->get('/safaris?page=999')->assertNotFound();
        \App\Models\User::create(['name' => 'Admin', 'email' => 'seo@example.test', 'password' => 'test-password', 'is_admin' => true]);
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/admin/dashboard')->assertRedirect();
    }

    public function test_destination_metadata_is_derived_from_its_content(): void
    {
        Destination::create(['name' => 'Mountain forest', 'slug' => 'mountain-forest', 'country' => 'Tanzania', 'summary' => 'Explore this particular forest.', 'status' => 'published']);
        $this->get('/destinations/mountain-forest')->assertOk()->assertSee('content="Explore this particular forest."', false)->assertSee('TouristDestination');
    }

    public function test_empty_catalogue_and_settings_cache_are_safe(): void
    {
        $this->get('/safaris')->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/safaris');
        \App\Models\SiteSetting::updateOrCreate(['key' => 'brand_name'], ['value' => 'First name']);
        $this->assertSame('First name', \App\Models\SiteSetting::publicValues()['brand_name']);
        \App\Models\SiteSetting::updateOrCreate(['key' => 'brand_name'], ['value' => 'Updated name']);
        $this->assertSame('Updated name', \App\Models\SiteSetting::publicValues()['brand_name']);
    }

    public function test_published_url_is_protected_and_json_cannot_close_script(): void
    {
        $tour = $this->tour(['title' => 'Forest </script><script>alert(1)</script>']);
        $html = $this->get('/safaris/'.$tour->slug)->assertOk()->getContent();
        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $tour->update(['slug' => 'changed-published-url']);
    }

    public function test_new_uploads_have_responsive_copies_without_replacing_original(): void
    {
        $path = \App\Support\PublicImage::store(\Illuminate\Http\UploadedFile::fake()->image('forest.png', 1800, 900), 'seo-testing');
        try {
            $attributes = (string) \App\Support\ResponsiveImage::attributes($path);
            $this->assertStringContainsString('480w', $attributes);
            $this->assertStringContainsString('960w', $attributes);
            $this->assertStringContainsString('width="1800"', $attributes);
            $this->assertFileExists(public_path(\App\Support\PublicImage::sourceFor($path)));
        } finally {
            \App\Support\PublicImage::delete($path);
        }
        $this->assertFileDoesNotExist(public_path(substr($path, 0, -5).'-480w.webp'));
    }
}
