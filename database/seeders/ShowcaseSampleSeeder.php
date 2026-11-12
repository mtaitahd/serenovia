<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Support\PublicImage;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShowcaseSampleSeeder extends Seeder
{
    /** Add an editable, repeat-safe starter catalogue using the photographs in public/images. */
    public function run(): void
    {
        $photos = [
            ['PXL_20260529_143301862.jpg.jpeg', 'Zebra herds across the open plains', 'A wide herd of zebras moving through golden grass beneath a broad East African sky.', 'Wildlife', 1],
            ['PXL_20260529_143344197.jpg.jpeg', 'Elephant among the zebra herds', 'An elephant and zebras sharing open savannah grassland.', 'Wildlife', 2],
            ['PXL_20260601_082001901.jpg.jpeg', 'Safari vehicle on the plains', 'A Serenovia safari vehicle ready for a day exploring the savannah.', 'Safari', 3],
            ['PXL_20260601_050038469.jpg.jpeg', 'A warm welcome in Maasai country', 'Guests sharing a cultural visit with Maasai hosts in traditional dress.', 'Culture', 4],
            ['PXL_20260531_103016305.MP.jpg.jpeg', 'Handmade beadwork and gourds', 'Colourful handmade beaded gourds displayed at a local craft market.', 'Culture', 5],
            ['PXL_20260601_082234108.jpg.jpeg', 'A baobab landmark on safari', 'Travellers pause beside a towering baobab in the dry-season landscape.', 'Safari', 6],
            ['PXL_20260529_150114119.jpg.jpeg', 'Zebras in the evening light', 'A close view of zebras grazing in warm late-afternoon light.', 'Wildlife', 10],
            ['PXL_20260529_150122325.jpg.jpeg', 'Zebra herd beneath the acacia trees', 'Zebras graze in a sunlit grassland bordered by acacia trees.', 'Wildlife', 11],
            ['PXL_20260529_151812404.jpg.jpeg', 'Vultures on the savannah', 'A group of scavenger birds gathers in tall grass on the savannah.', 'Wildlife', 12],
            ['PXL_20260529_151829505.jpg.jpeg', 'Learning traditional archery', 'Visitors learn about traditional archery during a community experience.', 'Culture', 13],
            ['PXL_20260529_151831165.jpg.jpeg', 'A shared archery experience', 'A local host demonstrates archery to visitors beneath the trees.', 'Culture', 14],
            ['PXL_20260531_090301978.jpg.jpeg', 'Community archery demonstration', 'A group gathers for a traditional archery demonstration.', 'Culture', 15],
            ['PXL_20260531_090352233.MP.jpg.jpeg', 'Practising with a local guide', 'Visitors take part in an outdoor archery lesson with a local guide.', 'Culture', 16],
            ['PXL_20260531_090355652.MP.jpg.jpeg', 'Discovering local traditions', 'A close moment from a hands-on cultural visit.', 'Culture', 17],
            ['PXL_20260531_090359537.jpg.jpeg', 'A cultural gathering in the shade', 'Visitors and community members meet beneath the acacia canopy.', 'Culture', 18],
            ['PXL_20260531_091538548.MP.jpg.jpeg', 'A village visit beneath the rocks', 'Families gather beneath a dramatic rock overhang during a cultural visit.', 'Culture', 19],
            ['PXL_20260531_091540630.MP.jpg.jpeg', 'Meeting local hosts', 'A group portrait with local hosts during a community visit.', 'Culture', 20],
            ['PXL_20260531_091756658.MP.jpg.jpeg', 'Stories shared beneath the shelter', 'Local families gather beneath a natural rock shelter.', 'Culture', 21],
            ['PXL_20260531_092420312.jpg.jpeg', 'Traditional craft in progress', 'A local craftsperson demonstrates traditional stone-working techniques.', 'Culture', 22],
            ['PXL_20260531_103209241.jpg.jpeg', 'Colourful handmade gourds', 'Handmade gourds decorated with detailed East African beadwork.', 'Culture', 23],
            ['PXL_20260531_103210598.jpg.jpeg', 'Beadwork made by hand', 'A detailed view of colourful beadwork on traditional crafts.', 'Culture', 24],
            ['PXL_20260531_111510032.jpg.jpeg', 'Learning a traditional skill', 'A visitor tries a traditional household skill with local guidance.', 'Culture', 25],
            ['PXL_20260531_134802727.jpg.jpeg', 'Rhythm and music', 'Local musicians share a lively drumming experience with visitors.', 'Culture', 26],
            ['PXL_20260601_043645880.jpg.jpeg', 'A hands-on village experience', 'A visitor learns a traditional technique during a village visit.', 'Culture', 27],
            ['PXL_20260601_044300710.jpg.jpeg', 'Music, drums and local stories', 'Musicians bring a cultural experience to life with traditional drums.', 'Culture', 28],
            ['PXL_20260601_044302183.jpg.jpeg', 'Welcomed by the community', 'Guests meet local hosts during a cultural journey in Tanzania.', 'Culture', 29],
            ['PXL_20260601_044303199.jpg.jpeg', 'A day in Maasai country', 'A cultural gathering in the open grassland at golden hour.', 'Culture', 30],
        ];

        $gallery = [];
        foreach ($photos as [$filename, $title, $alt, $category, $sort]) {
            $existing = GalleryImage::where('title', $title)->first();
            if ($existing) {
                $gallery[$filename] = $existing;
                continue;
            }

            $source = public_path('images/'.$filename);
            if (! is_file($source)) {
                continue;
            }

            $upload = new UploadedFile($source, $filename, 'image/jpeg', null, true);
            $path = PublicImage::store($upload, 'gallery', 84);
            $sourcePath = PublicImage::sourceFor($path);
            $gallery[$filename] = GalleryImage::create([
                'path' => $path,
                'source_path' => $sourcePath,
                'original_size' => filesize(public_path($sourcePath)),
                'optimized_size' => filesize(public_path($path)),
                'title' => $title,
                'alt_text' => $alt,
                'category' => $category,
                'sort_order' => $sort,
            ]);
        }

        $photo = fn (string $filename): ?GalleryImage => $gallery[$filename] ?? GalleryImage::where('title', $this->titleFor($filename))->first();

        // Make these photographs the default hero while retaining the original uploaded photo in the gallery.
        $hero = $photo('PXL_20260529_143301862.jpg.jpeg');
        if ($hero) {
            SiteSetting::updateOrCreate(['key' => 'hero_image'], ['value' => $hero->path]);
        }

        $destinations = [
            ['name' => 'Tarangire National Park', 'slug' => 'tarangire-national-park', 'tagline' => 'Baobabs, elephants and quiet horizons', 'summary' => 'A relaxed safari landscape known for ancient baobabs, open plains and memorable wildlife encounters.', 'description' => 'Explore a quieter side of the northern safari circuit, with time to notice the baobab silhouettes, changing light and wildlife along the way.', 'image' => 'PXL_20260601_082234108.jpg.jpeg', 'sort_order' => 5],
            ['name' => 'Lake Eyasi', 'slug' => 'lake-eyasi', 'tagline' => 'A landscape shaped by community and place', 'summary' => 'A dry-country lake basin where cultural visits and wide-open scenery create a slower-paced journey.', 'description' => 'Lake Eyasi offers a chance to experience a different rhythm of Tanzania, with community-led visits and expansive landscapes.', 'image' => 'PXL_20260531_091538548.MP.jpg.jpeg', 'sort_order' => 6],
            ['name' => 'Arusha', 'slug' => 'arusha', 'tagline' => 'The gateway to the northern circuit', 'summary' => 'A lively northern Tanzania base for starting or ending a safari, with green foothills and a welcoming city atmosphere.', 'description' => 'Set between the northern parks and Mount Meru, Arusha is a natural place to begin a journey, meet your guide and settle into Tanzania.', 'image' => 'PXL_20260601_082001901.jpg.jpeg', 'sort_order' => 7],
        ];

        foreach ($destinations as $destination) {
            $image = $photo($destination['image']);
            $attributes = $destination;
            unset($attributes['image']);
            $attributes['country'] = 'Tanzania';
            $attributes['image_path'] = $image?->path;
            $attributes['is_featured'] = false;
            $attributes['status'] = 'published';
            Destination::firstOrCreate(['slug' => $destination['slug']], $attributes);
        }

        $categoryData = [
            ['Safari & Wildlife', 'safari-wildlife', 'Wildlife-focused journeys across Tanzania.', 1],
            ['Culture & Community', 'culture-community', 'Community visits and cultural experiences led with respect.', 2],
            ['Northern Circuit', 'northern-circuit', 'Journeys through Tanzania’s classic northern landscapes.', 3],
        ];
        $categories = [];
        foreach ($categoryData as [$name, $slug, $description, $sort]) {
            $categories[$slug] = TourCategory::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'is_active' => true, 'sort_order' => $sort,
            ]);
        }

        $serengeti = Destination::where('slug', 'serengeti')->first();
        $tarangire = Destination::where('slug', 'tarangire-national-park')->first();
        $eyasi = Destination::where('slug', 'lake-eyasi')->first();

        $sampleStays = [
            ['name' => 'Sample Serengeti Tented Camp', 'slug' => 'sample-serengeti-tented-camp', 'destination' => $serengeti, 'tier' => 'mid_range', 'location' => 'Serengeti area', 'description' => 'Example mid-range safari camp for planning previews. Confirm the accommodation partner and availability before accepting a booking.', 'amenities' => ['En-suite tent', 'Dining tent', 'Safari location'], 'image' => 'PXL_20260529_143344197.jpg.jpeg'],
            ['name' => 'Sample Tarangire Safari Lodge', 'slug' => 'sample-tarangire-safari-lodge', 'destination' => $tarangire, 'tier' => 'mid_range', 'location' => 'Tarangire area', 'description' => 'Example lodge listing for planning previews. Confirm the accommodation partner and availability before accepting a booking.', 'amenities' => ['Lodge room', 'Dining area', 'Safari location'], 'image' => 'PXL_20260601_082234108.jpg.jpeg'],
            ['name' => 'Sample Lake Eyasi Guesthouse', 'slug' => 'sample-lake-eyasi-guesthouse', 'destination' => $eyasi, 'tier' => 'budget', 'location' => 'Lake Eyasi area', 'description' => 'Example guesthouse listing for planning previews. Confirm the accommodation partner and availability before accepting a booking.', 'amenities' => ['Guest room', 'Local host', 'Breakfast'], 'image' => 'PXL_20260531_091540630.MP.jpg.jpeg'],
        ];
        $stays = [];
        foreach ($sampleStays as $stay) {
            $record = Accommodation::firstOrCreate(['slug' => $stay['slug']], [
                'name' => $stay['name'], 'destination_id' => $stay['destination']?->id,
                'tier' => $stay['tier'], 'location' => $stay['location'], 'description' => $stay['description'],
                'amenities' => $stay['amenities'], 'currency' => 'USD', 'price_from' => null,
                'image_path' => $photo($stay['image'])?->path, 'is_featured' => false,
                'status' => 'published', 'sort_order' => 90,
            ]);
            $stays[$stay['slug']] = $record;
        }

        $tours = [
            [
                'title' => 'Serengeti Plains & Ngorongoro Crater', 'slug' => 'serengeti-plains-ngorongoro-crater',
                'destination' => $serengeti, 'type' => 'safari', 'days' => 5, 'nights' => 4,
                'summary' => 'A sample five-day northern-circuit safari with open plains, wildlife viewing and a day in the Ngorongoro highlands.',
                'description' => 'A sample safari journey designed to showcase the variety of Tanzania’s northern circuit. Spend unhurried time on the plains, travel with a local guide and descend into the Ngorongoro highlands. Park access, route and accommodation are confirmed around your travel dates.',
                'itinerary' => "Day 1: Arrive in Arusha and meet your local safari team.\nDay 2: Travel toward the Serengeti with wildlife viewing along the way.\nDay 3: Spend a full day exploring the Serengeti plains.\nDay 4: Continue toward the Ngorongoro highlands for a crater-area stay.\nDay 5: Enjoy a morning crater visit, then return to Arusha.",
                'inclusions' => "Private safari vehicle and local driver-guide\nAccommodation as confirmed in the final proposal\nMeals and park fees as itemized in the final quote",
                'exclusions' => "International flights and visa fees\nTravel insurance and personal purchases\nAny item not listed in the final written proposal",
                'image' => 'PXL_20260529_143301862.jpg.jpeg', 'category_slugs' => ['safari-wildlife', 'northern-circuit'],
                'stay' => 'sample-serengeti-tented-camp',
            ],
            [
                'title' => 'Tarangire Baobabs & Elephant Country', 'slug' => 'tarangire-baobabs-elephant-country',
                'destination' => $tarangire, 'type' => 'safari', 'days' => 3, 'nights' => 2,
                'summary' => 'A sample short safari built around Tarangire’s baobab country and its remarkable elephant herds.',
                'description' => 'A compact sample safari for travellers with a few days to explore. Follow the seasonal rhythm of the park with a knowledgeable guide, leaving room for wildlife sightings and slow moments beneath the baobabs. Routes and park conditions are confirmed before booking.',
                'itinerary' => "Day 1: Depart Arusha for Tarangire and enjoy an afternoon game drive.\nDay 2: Explore the park with a full-day wildlife drive and picnic lunch.\nDay 3: Take a final morning drive before returning to Arusha.",
                'inclusions' => "Private safari vehicle and local driver-guide\nAccommodation as confirmed in the final proposal\nMeals and park fees as itemized in the final quote",
                'exclusions' => "International flights and visa fees\nTravel insurance and personal purchases\nAny item not listed in the final written proposal",
                'image' => 'PXL_20260601_082234108.jpg.jpeg', 'category_slugs' => ['safari-wildlife', 'northern-circuit'],
                'stay' => 'sample-tarangire-safari-lodge',
            ],
            [
                'title' => 'Lake Eyasi & Cultural Encounters', 'slug' => 'lake-eyasi-cultural-encounters',
                'destination' => $eyasi, 'type' => 'cultural', 'days' => 3, 'nights' => 2,
                'summary' => 'A sample cultural journey shaped around community-led visits, local stories and open landscapes.',
                'description' => 'A sample itinerary that pairs a slower stay near Lake Eyasi with a respectful, locally guided community visit. Experiences are arranged with hosts in advance, and the final plan follows their guidance and availability.',
                'itinerary' => "Day 1: Travel from Arusha to the Lake Eyasi area and settle in.\nDay 2: Join a community-led visit and learn about local traditions from your hosts.\nDay 3: Enjoy a final morning in the area before returning to Arusha.",
                'inclusions' => "Private transfer and local guide\nCommunity visit arranged with local hosts\nAccommodation and meals as confirmed in the final proposal",
                'exclusions' => "International flights and visa fees\nTravel insurance and personal purchases\nAny item not listed in the final written proposal",
                'image' => 'PXL_20260531_091538548.MP.jpg.jpeg', 'category_slugs' => ['culture-community'],
                'stay' => 'sample-lake-eyasi-guesthouse',
            ],
        ];

        foreach ($tours as $index => $tourData) {
            $cover = $photo($tourData['image']);
            $tour = TourPackage::firstOrCreate(['slug' => $tourData['slug']], [
                'title' => $tourData['title'], 'destination_id' => $tourData['destination']?->id,
                'starting_point' => 'Arusha', 'ending_point' => 'Arusha', 'tour_type' => $tourData['type'],
                'duration_days' => $tourData['days'], 'duration_nights' => $tourData['nights'],
                'currency' => 'USD', 'max_group_size' => 6, 'summary' => $tourData['summary'],
                'description' => $tourData['description'], 'itinerary' => $tourData['itinerary'],
                'inclusions' => $tourData['inclusions'], 'exclusions' => $tourData['exclusions'],
                'image_path' => $cover ? PublicImage::copy($cover->path, 'tours') : null,
                'is_featured' => $index === 0, 'status' => 'published', 'published_at' => now(),
            ]);
            $tour->categories()->syncWithoutDetaching(collect($tourData['category_slugs'])->map(fn ($slug) => $categories[$slug]->id)->all());

            $stay = $stays[$tourData['stay']];
            if (! DB::table('tour_package_accommodations')->where('tour_package_id', $tour->id)->exists()) {
                DB::table('tour_package_accommodations')->insert([
                    'tour_package_id' => $tour->id,
                    'accommodation_id' => $stay->id,
                    'day_number' => 1,
                    'tier' => $stay->tier,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function titleFor(string $filename): string
    {
        // Titles are stable keys for making this seeder safe to run more than once.
        return match ($filename) {
            'PXL_20260529_143301862.jpg.jpeg' => 'Zebra herds across the open plains',
            'PXL_20260529_143344197.jpg.jpeg' => 'Elephant among the zebra herds',
            'PXL_20260601_082001901.jpg.jpeg' => 'Safari vehicle on the plains',
            'PXL_20260601_050038469.jpg.jpeg' => 'A warm welcome in Maasai country',
            'PXL_20260531_103016305.MP.jpg.jpeg' => 'Handmade beadwork and gourds',
            'PXL_20260601_082234108.jpg.jpeg' => 'A baobab landmark on safari',
            'PXL_20260529_150114119.jpg.jpeg' => 'Zebras in the evening light',
            'PXL_20260529_150122325.jpg.jpeg' => 'Zebra herd beneath the acacia trees',
            'PXL_20260529_151812404.jpg.jpeg' => 'Vultures on the savannah',
            'PXL_20260529_151829505.jpg.jpeg' => 'Learning traditional archery',
            'PXL_20260529_151831165.jpg.jpeg' => 'A shared archery experience',
            'PXL_20260531_090301978.jpg.jpeg' => 'Community archery demonstration',
            'PXL_20260531_090352233.MP.jpg.jpeg' => 'Practising with a local guide',
            'PXL_20260531_090355652.MP.jpg.jpeg' => 'Discovering local traditions',
            'PXL_20260531_090359537.jpg.jpeg' => 'A cultural gathering in the shade',
            'PXL_20260531_091538548.MP.jpg.jpeg' => 'A village visit beneath the rocks',
            'PXL_20260531_091540630.MP.jpg.jpeg' => 'Meeting local hosts',
            'PXL_20260531_091756658.MP.jpg.jpeg' => 'Stories shared beneath the shelter',
            'PXL_20260531_092420312.jpg.jpeg' => 'Traditional craft in progress',
            'PXL_20260531_103209241.jpg.jpeg' => 'Colourful handmade gourds',
            'PXL_20260531_103210598.jpg.jpeg' => 'Beadwork made by hand',
            'PXL_20260531_111510032.jpg.jpeg' => 'Learning a traditional skill',
            'PXL_20260531_134802727.jpg.jpeg' => 'Rhythm and music',
            'PXL_20260601_043645880.jpg.jpeg' => 'A hands-on village experience',
            'PXL_20260601_044300710.jpg.jpeg' => 'Music, drums and local stories',
            'PXL_20260601_044302183.jpg.jpeg' => 'Welcomed by the community',
            'PXL_20260601_044303199.jpg.jpeg' => 'A day in Maasai country',
            default => Str::headline(pathinfo($filename, PATHINFO_FILENAME)),
        };
    }
}
