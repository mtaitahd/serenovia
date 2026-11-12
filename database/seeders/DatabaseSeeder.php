<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (config('serenova.settings', []) as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $destinations = [
            ['name' => 'Serengeti', 'slug' => 'serengeti', 'tagline' => 'The plains go on forever', 'summary' => 'Open horizons, changing light and the timeless rhythm of the savannah.', 'sort_order' => 1],
            ['name' => 'Ngorongoro', 'slug' => 'ngorongoro', 'tagline' => 'A world within a world', 'summary' => 'A remarkable highland landscape shaped by wildlife, volcanic history and Maasai culture.', 'sort_order' => 2],
            ['name' => 'Kilimanjaro', 'slug' => 'kilimanjaro', 'tagline' => 'The roof of Africa', 'summary' => 'A mountain journey through changing landscapes toward a summit above the clouds.', 'sort_order' => 3],
            ['name' => 'Zanzibar', 'slug' => 'zanzibar', 'tagline' => 'Where the ocean slows time', 'summary' => 'Island days shaped by Indian Ocean breezes, Swahili heritage and a slower pace.', 'sort_order' => 4],
        ];
        foreach ($destinations as $destination) {
            Destination::updateOrCreate(
                ['slug' => $destination['slug']],
                [...$destination, 'country' => 'Tanzania', 'is_featured' => true, 'status' => 'published']
            );
        }

        $articles = [
            [
                'title' => 'Your first safari: learning to slow down',
                'slug' => 'your-first-safari',
                'category' => 'Safari',
                'excerpt' => 'A safari is not a checklist. It is the hush before sunrise, a guide who knows where to pause, and the moment the plains begin to move.',
                'body' => "Leave room in the day for the unexpected. Some of the most memorable moments arrive between the famous sightings: a herd crossing the horizon, the changing light on the grass, or a quiet conversation with your guide.\n\nEvery park has its own rhythm. Let your interests and the time you have shape the route, then give each place time to unfold. A thoughtful safari is measured in moments, not miles.",
            ],
            [
                'title' => 'On the mountain, every step has a story',
                'slug' => 'kilimanjaro-every-step',
                'category' => 'Kilimanjaro',
                'excerpt' => 'The climb begins long before the summit. It starts with a trail, a steady pace and a team sharing the same horizon.',
                'body' => "Kilimanjaro changes with every step: forest shade gives way to open moorland, then to a high, wide quiet. Each day brings a new landscape and a different view of the mountain.\n\nThere is no need to rush the experience. Notice the small details around you and let your local mountain crew lead the way. Reaching the summit is a moment to remember; so is the journey there.",
            ],
            [
                'title' => 'Zanzibar, best explored unhurried',
                'slug' => 'zanzibar-unhurried',
                'category' => 'Island life',
                'excerpt' => 'Beyond the shoreline, island life reveals itself in carved doorways, market colours and the scent of spices on the breeze.',
                'body' => "Give yourself time to wander through Stone Town's narrow lanes, notice the details in its old doors, and pause when a small café or market catches your eye. The island's character is found in these everyday encounters.\n\nThen follow the coast at your own pace. A few unplanned hours can turn a beach stay into a richer part of your Tanzania journey.",
            ],
        ];
        foreach ($articles as $index => $article) {
            Article::updateOrCreate(
                ['slug' => $article['slug']],
                [...$article, 'status' => 'published', 'is_featured' => $index === 0, 'published_at' => now()->subDays($index)]
            );
        }

        $faqs = [
            ['question' => 'When is the best time to visit Tanzania?', 'answer' => "There isn't one perfect month for every journey. Wildlife, weather and experiences vary by region and season. Tell us what you'd love to see and when you can travel; we'll help you choose a route that fits."],
            ['question' => 'Do I need a visa to visit Tanzania?', 'answer' => 'Entry requirements depend on your nationality and travel details. Please check the official Tanzania Immigration visa guidelines at visa.immigration.go.tz/guidelines and confirm requirements with Immigration before booking or travelling.'],
            ['question' => 'What is included in my safari?', 'answer' => "Every journey is different. Your written itinerary and quote will list what's included—such as accommodation, transport, guiding, meals or park fees—and anything arranged separately."],
            ['question' => 'Can you customize the itinerary?', 'answer' => 'Yes. Share your travel dates, interests, pace and budget, and we will help shape a Tanzania journey around what matters to you.'],
            ['question' => 'How far in advance should I book?', 'answer' => "Availability depends on your dates, route and the places you want to stay. Get in touch as early as you can, especially if your dates are fixed, so we can check availability."],
            ['question' => 'What should I pack for a safari?', 'answer' => "Comfortable neutral layers, sturdy walking shoes, sun protection and any personal medication are useful starting points. Conditions vary by itinerary, so we'll share a tailored packing list once your route is planned."],
        ];
        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], [...$faq, 'sort_order' => $index + 1, 'is_active' => true]);
        }
    }
}
