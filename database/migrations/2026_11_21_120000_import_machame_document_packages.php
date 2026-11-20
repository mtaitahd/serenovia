<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $document = json_decode(file_get_contents(database_path('data/machame-route-2026-11-21.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($document): void {
            $created = [];
            $ids = [];
            $destinationId = DB::table('destinations')->whereIn('slug', ['kilimanjaro', 'mount-kilimanjaro'])->value('id');
            foreach ($document['tours'] as $tour) {
                // Never overwrite an existing package or photographs added by an administrator.
                $existing = DB::table('tour_packages')->where('slug', $tour['slug'])->value('id');
                if ($existing) {
                    $ids[] = (int) $existing;
                    continue;
                }
                $details = $tour['package_details'];
                $tour['package_details'] = json_encode($details, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $tour['itinerary_days'] = json_encode($tour['itinerary_days'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $id = DB::table('tour_packages')->insertGetId($tour + [
                    'destination_id' => $destinationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $ids[] = $id;
                $created[$id] = $details;
            }
            foreach ($created as $id => $details) {
                $details['itinerary_option_ids'] = array_values(array_diff($ids, [$id]));
                DB::table('tour_packages')->where('id', $id)->update([
                    'package_details' => json_encode($details, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Content may have been edited or published since import; rolling back must preserve it.
    }
};
