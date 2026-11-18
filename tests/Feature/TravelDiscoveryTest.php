<?php

namespace Tests\Feature;

require_once __DIR__.'/../TestCase.php';

use App\Models\Destination;
use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_paginates_and_preserves_duration_and_currency_price_filters(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            TourPackage::create(['title' => "Eligible safari $i", 'slug' => "eligible-$i",
                'tour_type' => 'safari', 'duration_days' => 3, 'duration_nights' => 2,
                'currency' => 'USD', 'price_from' => 750, 'status' => 'published']);
        }
        foreach ([['EUR', 3], ['USD', 7]] as [$currency, $days]) {
            TourPackage::create(['title' => "Excluded $currency safari", 'slug' => "excluded-$currency",
                'tour_type' => 'safari', 'duration_days' => $days, 'duration_nights' => $days - 1,
                'currency' => $currency, 'price_from' => 750, 'status' => 'published']);
        }

        $url = '/safaris?duration%5B0%5D=2-5&price_currency=USD&min_price=700&max_price=800';
        $first = $this->get($url)->assertOk()->assertSee('10 journeys found')->assertSee('aria-label="Page 2"', false)
            ->assertDontSee('Excluded EUR safari')->assertDontSee('Excluded USD safari');
        $this->assertSame(9, substr_count($first->getContent(), 'class="tour-tile"'));
        $second = $this->get($url.'&page=2')->assertOk()->assertSee('Eligible safari 1');
        $this->assertSame(1, substr_count($second->getContent(), 'class="tour-tile"'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        app('url')->forceRootUrl('http://localhost');
    }

    public function test_travel_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Find your trip')->assertSee('hero-trip-finder', false);
        $this->get('/about-us')->assertOk()->assertSee('About Serenovia Africa Safaris');
        $this->get('/responsible-travel')->assertOk()->assertSee('Respect local communities');
        $this->get('/travel-guide')->assertOk()->assertSee('Travel stories');
    }

    public function test_finder_matches_destinations_and_excludes_long_and_unpublished_trips(): void
    {
        $destination = Destination::create(['name' => 'Lake Eyasi', 'slug' => 'lake-eyasi', 'country' => 'Tanzania', 'status' => 'published']);
        foreach ([['Short discovery', 3, 'published'], ['Extended discovery', 9, 'published'], ['Private draft', 3, 'draft']] as [$title, $days, $status]) {
            TourPackage::create(['title' => $title, 'slug' => str()->slug($title), 'destination_id' => $destination->id,
                'tour_type' => 'safari', 'duration_days' => $days, 'duration_nights' => $days - 1, 'currency' => 'USD', 'status' => $status]);
        }

        $this->get('/safaris?q=Eyasi&max_days=3')->assertOk()->assertSee('Short discovery')
            ->assertDontSee('Extended discovery')->assertDontSee('Private draft');
    }
}
