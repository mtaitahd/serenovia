<?php
namespace Tests\Feature;
require_once __DIR__.'/../TestCase.php';

use App\Models\{Article, Destination, TourPackage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_discovery_is_stateless_valid_and_independent_of_user_agent_and_ip(): void
    {
        config(['app.url' => 'https://www.serenoviaafrica.com', 'session.driver' => 'not-a-session-driver']);
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; Google-InspectionTool/1.0;)',
            '',
        ];
        foreach ($agents as $index => $agent) {
            $server = ['REMOTE_ADDR' => '192.0.2.'.($index + 1)];
            if ($agent !== '') $server['HTTP_USER_AGENT'] = $agent;
            foreach (['GET', 'HEAD'] as $method) {
                foreach (['/sitemap.xml' => 'application/xml', '/robots.txt' => 'text/plain'] as $path => $type) {
                    $response = $this->call($method, $path, [], [], [], $server)->assertOk();
                    $this->assertStringStartsWith($type, $response->headers->get('Content-Type'));
                    $this->assertSame([], $response->headers->getCookies());
                    $this->assertFalse($response->headers->has('X-Robots-Tag'));
                    if ($method === 'GET' && $path === '/sitemap.xml') {
                        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
                        $xml = simplexml_load_string($response->getContent());
                        $this->assertNotFalse($xml);
                        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $xml->getDocNamespaces()['']);
                        foreach ($xml->url as $entry) $this->assertStringStartsWith('https://www.serenoviaafrica.com/', (string) $entry->loc);
                    }
                    if ($method === 'GET' && $path === '/robots.txt') $response->assertSee("Disallow:\n\nSitemap: https://www.serenoviaafrica.com/sitemap.xml", false);
                }
            }
        }
    }

    public function test_dynamic_entries_are_public_current_and_query_count_is_bounded(): void
    {
        config(['app.url' => 'https://www.serenoviaafrica.com']);
        $tour = TourPackage::create(['title' => 'Audit trek', 'slug' => 'audit-trek', 'tour_type' => 'mountain', 'duration_days' => 1, 'duration_nights' => 0, 'currency' => 'USD', 'status' => 'published']);
        Destination::create(['name' => 'Audit forest', 'slug' => 'audit-forest', 'country' => 'Tanzania', 'status' => 'published']);
        Article::create(['title' => 'Audit story', 'slug' => 'audit-story', 'category' => 'Travel', 'body' => 'Travel notes', 'status' => 'published', 'published_at' => now()->subDay()]);
        Article::create(['title' => 'Future', 'slug' => 'audit-future', 'category' => 'Travel', 'body' => 'Future', 'status' => 'published', 'published_at' => now()->addDay()]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('/safaris/audit-trek')->assertSee('/destinations/audit-forest')->assertSee('/journal/audit-story')->assertDontSee('audit-future');
        $this->assertCount(3, DB::getQueryLog());
        DB::disableQueryLog();
        $xml = simplexml_load_string($response->getContent());
        $locations = [];
        foreach ($xml->url as $entry) {
            $url = (string) $entry->loc;
            $locations[] = $url;
            $page = $this->get(parse_url($url, PHP_URL_PATH))->assertOk();
            $page->assertSee('rel="canonical" href="'.$url.'"', false);
            $page->assertSee('index, follow, max-image-preview:large');
            if ($url === 'https://www.serenoviaafrica.com/safaris/audit-trek') $this->assertSame($tour->updated_at->toAtomString(), (string) $entry->lastmod);
        }
        $this->assertSame($locations, array_values(array_unique($locations)));
        $tour->update(['status' => 'draft']);
        $this->get('/sitemap.xml')->assertDontSee('/safaris/audit-trek');
        $this->get('/safaris/audit-trek')->assertNotFound();
        $tour->delete();
        $this->get('/sitemap.xml')->assertDontSee('/safaris/audit-trek');
        $this->get('/missing-audit-page')->assertNotFound();
        $this->get('/admin/dashboard')->assertRedirect();
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }
}
