<?php

namespace Tests\Feature;

use App\Models\{GalleryImage, SiteSetting, TourPackage, User};
use App\Support\{HomepageContent, PublicImage};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HomepageEditorTest extends TestCase
{
    use RefreshDatabase;
    private array $images = [];
    private array $videos = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        app('url')->forceRootUrl('http://localhost');
    }

    protected function tearDown(): void
    {
        foreach ($this->images as $path) PublicImage::delete($path);
        foreach ($this->videos as $path) @unlink(public_path($path));
        parent::tearDown();
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    private function payload(string $section): array
    {
        $values = HomepageContent::values();
        $content = [];
        foreach (HomepageContent::sections()[$section]['fields'] as $key => $field) {
            if (! in_array($field['type'], ['image','video'])) $content[$key] = $values[$key];
        }
        return ['section' => $section, 'content' => $content];
    }

    public function test_admin_sections_show_current_defaults_and_video_and_require_authentication(): void
    {
        $this->get('/admin/website-content/homepage')->assertRedirect();
        $this->admin();
        foreach (HomepageContent::sections() as $section => $definition) {
            $this->get('/admin/website-content/homepage?section='.$section)
                ->assertOk()->assertSee($definition['label'])->assertSee('Save this section');
        }
        $this->get('/admin/website-content/homepage?section=hero')->assertSee('home-ngorongoro-elephants.webm')->assertSee('<video', false);
        $this->get('/admin/website-content/homepage?section=wrong')->assertNotFound();
    }

    public function test_saving_one_section_updates_the_public_page_and_preserves_others(): void
    {
        $this->admin();
        SiteSetting::create(['key' => 'home_journeys_title', 'value' => 'Existing journeys']);
        $payload = $this->payload('intro');
        $payload['content']['home_intro_title'] = "New welcome\nMade for you";
        $payload['content']['home_intro_lead'] = 'An introduction you can edit.';
        $payload['content']['home_intro_primary_url'] = '/contact?source=home';
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Existing journeys', SiteSetting::get('home_journeys_title'));
        $this->get('/')->assertOk()->assertSee('New welcome')->assertSee('An introduction you can edit.')->assertSee('source=home');
        $payload['content']['home_intro_enabled'] = '0';
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('New welcome');
    }

    public function test_gallery_selection_changes_real_hero_image_without_deleting_gallery_source(): void
    {
        $this->admin();
        $original = PublicImage::store(UploadedFile::fake()->image('gallery.jpg', 60, 40), 'tests/homepage');
        $this->images[] = $original;
        $image = GalleryImage::create(['path' => $original, 'title' => 'Selected elephant', 'category' => 'Wildlife']);
        $payload = $this->payload('hero');
        $payload['gallery'] = ['hero_image' => $image->id];
        $payload['content']['home_hero_video_enabled'] = '0';
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasNoErrors();
        $saved = SiteSetting::get('hero_image');
        $this->images[] = $saved;
        $this->assertNotSame($original, $saved);
        $this->assertFileExists(public_path($original));
        $this->assertFileExists(public_path($saved));
        $this->get('/')->assertSee($saved)->assertDontSee('class="home-hero-video"', false);
        $this->get('/admin/website-content/homepage?section=hero')->assertSee($saved);
    }

    public function test_video_upload_is_previewed_and_rendered_but_invalid_media_and_unsafe_links_are_rejected(): void
    {
        $this->admin();
        $payload = $this->payload('hero');
        $payload['uploads'] = ['home_hero_video' => UploadedFile::fake()->createWithContent('wildlife.webm', file_get_contents(public_path('images/home-ngorongoro-elephants.webm')))];
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasNoErrors();
        $video = SiteSetting::get('home_hero_video');
        $this->videos[] = $video;
        $this->assertFileExists(public_path($video));
        $this->get('/')->assertSee($video);
        $this->get('/admin/website-content/homepage?section=hero')->assertSee($video);
        $payload['uploads'] = ['home_hero_video' => UploadedFile::fake()->createWithContent('bad.webm', '<?php echo "bad";')->mimeType('text/x-php')];
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasErrors('uploads.home_hero_video');
        unset($payload['uploads']);
        $payload['content']['home_primary_url'] = 'javascript:alert(1)';
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasErrors('content.home_primary_url');
        $this->assertSame($video, SiteSetting::get('home_hero_video'));
    }

    public function test_manual_cards_follow_order_use_actual_images_and_exclude_drafts(): void
    {
        $this->admin();
        $first = TourPackage::create(['title' => 'First safari', 'slug' => 'first', 'status' => 'published', 'image_path' => 'uploads/first.webp', 'duration_days' => 2]);
        $second = TourPackage::create(['title' => 'Second safari', 'slug' => 'second', 'status' => 'published', 'image_path' => 'uploads/second.webp', 'duration_days' => 3]);
        $draft = TourPackage::create(['title' => 'Secret draft', 'slug' => 'secret', 'status' => 'draft']);
        $payload = $this->payload('journeys') + ['selection_mode' => 'manual', 'record_ids' => [$second->id, $first->id]];
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasNoErrors();
        $this->assertSame([$second->id, $first->id], HomepageContent::records('journeys')->pluck('id')->all());
        $this->get('/')->assertOk()->assertSeeInOrder(['uploads/second.webp', 'Second safari', 'uploads/first.webp', 'First safari'])->assertDontSee('Secret draft');
        $payload['record_ids'] = [$draft->id];
        $this->put('/admin/website-content/homepage', $payload)->assertSessionHasErrors('record_ids.0');
        $second->update(['status' => 'draft']);
        $this->assertSame([$first->id], HomepageContent::records('journeys')->pluck('id')->all());
    }

    public function test_story_uses_selected_gallery_images_and_saved_captions(): void
    {
        $image = GalleryImage::create(['path' => 'uploads/story-unique.webp', 'title' => 'Story photo']);
        SiteSetting::create(['key' => 'our_story_gallery', 'value' => json_encode([['image_id' => $image->id, 'caption' => 'A real story caption']])]);
        $this->get('/')->assertOk()->assertSee('uploads/story-unique.webp')->assertSee('A real story caption');
    }
}
