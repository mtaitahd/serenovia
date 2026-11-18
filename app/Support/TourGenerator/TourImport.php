<?php

namespace App\Support\TourGenerator;

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\TourPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class TourImport
{
    public function errors(array $row): array
    {
        $validator = Validator::make(array_filter($row, fn ($value) => $value !== ''), [
            'tour_ref' => 'nullable|string|max:100', 'day_number' => 'required_with:tour_ref|nullable|integer|between:1,365',
            'day_title' => 'required|string|max:180', 'tour_type' => 'required|string|max:60',
            'country_code' => 'required|regex:/^[A-Z]{2}$/', 'destination_id' => 'required|exists:destinations,id',
            'category_id' => 'nullable|exists:tour_categories,id', 'start' => 'required|string|max:180',
            'end' => 'required|string|max:180', 'overnight' => 'nullable|same:end',
            'accommodation_id' => 'required_with:overnight|nullable|integer|exists:accommodations,id',
            'transport' => 'nullable|string|max:60',
            'travel_minutes' => 'nullable|integer|between:1,1440', 'position' => 'nullable|in:any,first,middle,last',
            'description' => 'required|string|max:12000', 'meals' => 'nullable|string|max:500',
            'distance' => 'nullable|string|max:120', 'walking_time' => 'nullable|string|max:120', 'altitude' => 'nullable|string|max:120',
            'latitude' => 'required_with:longitude|nullable|numeric|between:-90,90',
            'longitude' => 'required_with:latitude|nullable|numeric|between:-180,180',
            'overview' => 'nullable|string|max:12000', 'activities' => 'nullable|string|max:1000',
            'tour_title' => 'nullable|string|max:180', 'highlights' => 'nullable|string|max:12000',
            'inclusions' => 'nullable|string|max:12000', 'exclusions' => 'nullable|string|max:12000',
            'price_from' => 'nullable|numeric|min:0|max:99999999', 'currency' => 'nullable|regex:/^[A-Z]{3}$/',
            'gallery_ids' => ['nullable', 'regex:/^\d+(?:\s*\|\s*\d+){0,4}$/'],
            'transport_gallery_ids' => ['nullable', 'regex:/^\d+(?:\s*\|\s*\d+){0,4}$/'],
        ]);
        $validator->after(function ($validator) use ($row) {
            if (!empty($row['start']) && !empty($row['end']) && $this->key($row['start']) !== $this->key($row['end']) && empty($row['transport'])) {
                $validator->errors()->add('transport', 'Enter the verified transport mode for this route.');
            }
            if (!empty($row['accommodation_id'])) {
                $stay = Accommodation::find($row['accommodation_id']);
                if (empty($row['overnight']) || !$stay || $stay->status !== 'published' || (
                    $this->key($stay->location ?? '') !== $this->key($row['overnight'])
                    && $this->key($stay->destination?->name ?? '') !== $this->key($row['overnight'])
                )) {
                    $validator->errors()->add('accommodation_id', 'Choose a published accommodation at the overnight stop.');
                }
            }
            $gallery = $this->gallery($row);
            $transport = $this->values($row['transport_gallery_ids'] ?? '');
            if (GalleryImage::whereIn('id', $transport)->count() !== count($transport)) {
                $validator->errors()->add('transport_gallery_ids', 'Choose up to five existing gallery photos for transport.');
            }
            if (count($gallery) !== count($this->values($row['gallery_ids'] ?? ''))) {
                $validator->errors()->add('gallery_ids', 'Each image ID must match an existing gallery image.');
            }
        });
        return $validator->errors()->all();
    }

    public function approve(int $batch, int $user): void
    {
        DB::transaction(function () use ($batch, $user) {
            $claimed = DB::table('tour_gen_import_batches')->where('id', $batch)->where('status', 'review')->update(['status' => 'approving']);
            if (!$claimed) {
                throw ValidationException::withMessages(['import' => 'This batch is no longer awaiting review.']);
            }
            $records = DB::table('tour_gen_import_records')->where('batch_id', $batch)->where('status', '!=', 'skipped')->orderBy('id')->get();
            if ($records->isEmpty()) {
                throw ValidationException::withMessages(['import' => 'Select at least one valid itinerary row.']);
            }
            $groups = [];
            foreach ($records as $record) {
                $row = json_decode($record->mapped_payload, true, flags: JSON_THROW_ON_ERROR);
                if ($issues = $this->errors($row)) {
                    throw ValidationException::withMessages(['import' => $record->source_locator.': '.implode(' ', $issues)]);
                }
                $reference = $row['tour_ref'] ?? '';
                $group = $reference === '' ? 'block:'.$record->id : 'tour:'.$reference;
                $number = (int) (($row['day_number'] ?? '') ?: 1);
                if (isset($groups[$group][$number])) {
                    throw ValidationException::withMessages(['import' => "Duplicate day number for {$reference}."]);
                }
                $groups[$group][$number] = $row;
                $tour = isset($row['_tour_id']) ? TourPackage::findOrFail($row['_tour_id']) : null;
                $this->createBlock($row, $user, $record->id, $tour, $row['_day_key'] ?? null);
            }
            foreach ($groups as $group => $days) {
                if (!str_starts_with($group, 'tour:')) {
                    continue;
                }
                ksort($days);
                if (array_keys($days) !== range(1, count($days))) {
                    throw ValidationException::withMessages(['import' => "{$group} needs consecutive days starting at day 1."]);
                }
                $end = null;
                foreach ($days as $day) {
                    if ($end !== null && $end !== $this->key($day['start'])) {
                        throw ValidationException::withMessages(['import' => "{$group} has a break between itinerary stops."]);
                    }
                    $end = $this->key($day['end']);
                }
            }
            DB::table('tour_gen_import_records')->whereIn('id', $records->pluck('id'))->update(['status' => 'approved', 'updated_at' => now()]);
            DB::table('tour_gen_import_batches')->where('id', $batch)->update([
                'status' => 'approved', 'approved_by' => $user, 'approved_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function createBlock(array $row, int $user, ?int $record = null, ?TourPackage $tour = null, ?string $dayKey = null): int
    {
        $approval = ['status' => 'approved', 'approved_by' => $user, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()];
        $destination = Destination::findOrFail($row['destination_id']);
        $end = $this->location($row['end'], $row['country_code'], $destination->id, $approval);
        $start = $this->location($row['start'], $row['country_code'], null, $approval);
        $overnight = !empty($row['overnight']) ? $end : null;
        $payload = [
            'title' => $row['day_title'], 'details' => $row['description'], 'location' => $row['end'],
            'category_ids' => !empty($row['category_id']) ? [(int) $row['category_id']] : [],
            'images' => $this->gallery($row),
            'transport_gallery_ids' => $this->values($row['transport_gallery_ids'] ?? ''),
        ];
        $payload['package'] = array_intersect_key($row, array_flip(['tour_ref', 'tour_title', 'day_number', 'overview', 'highlights', 'inclusions', 'exclusions', 'price_from', 'currency']));
        foreach (['meals', 'distance', 'walking_time', 'altitude', 'latitude', 'longitude'] as $field) {
            if (isset($row[$field]) && $row[$field] !== '') {
                $payload[$field] = $row[$field];
            }
        }
        if ($tour && $dayKey) {
            $original = collect($tour->itinerary_days)->firstWhere('id', $dayKey);
            $payload['images'] = array_values(array_unique([...$payload['images'], ...($original['images'] ?? [])]));
        }
        if (filled($row['activities'] ?? null)) {
            $payload['details'] = mb_substr($payload['details']."\n\nActivities: ".$row['activities'], 0, 12000);
        }
        $block = DB::table('tour_gen_day_blocks')->insertGetId($approval + [
            'source_record_id' => $record, 'source_tour_package_id' => $tour?->id, 'source_day_key' => $dayKey,
            'start_location_id' => $start, 'end_location_id' => $end, 'overnight_location_id' => $overnight,
            'tour_type' => $row['tour_type'], 'position_rule' => ($row['position'] ?? '') ?: 'any',
            'day_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);
        if ($start !== $end) {
            $route = $this->findOrCreate('route_rules', [
                'from_location_id' => $start, 'to_location_id' => $end, 'transport_mode' => $row['transport'],
                'min_travel_minutes' => ($row['travel_minutes'] ?? '') ?: null,
                'max_travel_minutes' => ($row['travel_minutes'] ?? '') ?: null,
            ], $approval);
            DB::table('tour_gen_day_block_routes')->insert([
                'day_block_id' => $block, 'route_rule_id' => $route, 'position' => 1,
            ]);
        }
        foreach ($this->values($row['activities'] ?? '') as $index => $name) {
            $activity = $this->findOrCreate('activities', [
                'location_id' => $end, 'canonical_key' => Str::limit($this->key($name), 120, ''),
            ], $approval + ['name' => Str::limit($name, 255, '')]);
            DB::table('tour_gen_day_block_activities')->insert([
                'day_block_id' => $block, 'activity_id' => $activity, 'position' => $index + 1,
            ]);
        }
        if ($overnight) {
            $stay = Accommodation::findOrFail($row['accommodation_id']);
            $this->findOrCreate('accommodation_rules', [
                'location_id' => $overnight, 'accommodation_id' => $stay->id, 'tier' => $stay->tier,
            ], $approval);
        }
        return $block;
    }

    private function location(string $name, string $country, ?int $destination, array $approval): int
    {
        $key = Str::limit(strtolower($country).'-'.$this->key($name), 120, '');
        $id = $this->findOrCreate('locations', ['canonical_key' => $key], $approval + [
            'name' => $name, 'country_code' => $country, 'destination_id' => $destination,
        ]);
        if ($destination) {
            DB::table('tour_gen_locations')->where('id', $id)->whereNull('destination_id')->update(['destination_id' => $destination]);
        }
        return $id;
    }

    private function findOrCreate(string $table, array $match, array $attributes): int
    {
        $query = DB::table('tour_gen_'.$table);
        return $query->where($match)->where('status', 'approved')->value('id') ?? $query->insertGetId($match + $attributes);
    }

    private function key(string $value): string
    {
        return Str::slug($value);
    }

    private function values(string $value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', explode('|', $value)), fn ($item) => $item !== '')));
    }

    private function gallery(array $row): array
    {
        $ids = $this->values($row['gallery_ids'] ?? '');
        return GalleryImage::whereIn('id', $ids)->pluck('path')->all();
    }
}
