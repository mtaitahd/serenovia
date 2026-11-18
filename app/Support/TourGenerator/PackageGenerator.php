<?php

namespace App\Support\TourGenerator;

use App\Models\Destination;
use App\Models\TourPackage;
use App\Support\PublicImage;
use App\Support\SafariDays;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class PackageGenerator
{
    public function run(int $run, array $filters): void
    {
        $started = microtime(true);
        $deadline = $started + 18;
        $metrics = ['created' => 0, 'checked' => 0, 'duplicates' => 0, 'incompatible' => 0, 'limit_reached' => false];
        $locations = DB::table('tour_gen_locations')->where('status', 'approved')->get()->keyBy('id');
        $activities = DB::table('tour_gen_activities')->get()->keyBy('id');
        $routes = DB::table('tour_gen_route_rules')->get()->keyBy('id');
        $routeLinks = DB::table('tour_gen_day_block_routes')->orderBy('position')->get()->groupBy('day_block_id');
        $activityLinks = DB::table('tour_gen_day_block_activities')->orderBy('position')->get()->groupBy('day_block_id');
        $stays = DB::table('tour_gen_accommodation_rules as rule')->join('accommodations as stay', 'stay.id', '=', 'rule.accommodation_id')
            ->where('stay.status', 'published')->whereColumn('rule.tier', 'stay.tier')->where('rule.status', 'approved')
            ->when($filters['tier'] ?? null, fn ($query, $tier) => $query->where('rule.tier', $tier))
            ->select('rule.*')->orderBy('rule.id')->get()->groupBy('location_id');
        $byStart = [];
        $batches = $filters['import_batches'] ?? (empty($filters['import_batch']) ? [] : [$filters['import_batch']]);
        $blocks = DB::table('tour_gen_day_blocks')->where('status', 'approved')->orderBy('id')
            ->when($batches && $filters['source'] !== 'database', function ($query) use ($filters, $batches) {
                $query->where(function ($scope) use ($filters, $batches) {
                    $scope->whereIn('source_record_id', DB::table('tour_gen_import_records')
                        ->whereIn('batch_id', $batches)->where('status', 'approved')->select('id'));
                    if ($filters['source'] === 'hybrid') {
                        $scope->orWhereNull('source_record_id')->orWhereNotNull('source_tour_package_id');
                    }
                });
            })
            ->when($filters['source'] === 'database', fn ($query) => $query->where(fn ($source) => $source->whereNull('source_record_id')->orWhereNotNull('source_tour_package_id')))
            ->when($filters['source'] === 'import', fn ($query) => $query->whereNotNull('source_record_id')->whereNull('source_tour_package_id'))->get();

        foreach ($blocks as $block) {
            if (!$locations->has($block->start_location_id) || !$locations->has($block->end_location_id)) {
                continue;
            }
            $day = (array) $block;
            $day['day_payload'] = json_decode($block->day_payload, true, flags: JSON_THROW_ON_ERROR);
            $day['routes'] = ($routeLinks[$block->id] ?? collect())->map(function ($link) use ($routes) {
                $route = (array) $routes[$link->route_rule_id];
                $route['constraints'] = json_decode($route['constraints'] ?? '[]', true);
                return $route;
            })->all();
            $day['activities'] = ($activityLinks[$block->id] ?? collect())
                ->map(fn ($link) => (array) $activities[$link->activity_id])->all();
            $day['stays'] = ($stays[$block->overnight_location_id] ?? collect())->unique('tier')
                ->map(function ($stay) {
                    $rule = (array) $stay;
                    $rule['constraints'] = json_decode($stay->constraints ?? '[]', true);
                    return $rule;
                })->values()->all();
            $byStart[$block->start_location_id][] = $day;
        }

        $existing = [];
        foreach (TourPackage::with('accommodations')->lazyById(100) as $tour) {
            $days = $tour->itinerary_days ?: SafariDays::sections($tour);
            $existing[$tour->duration_days][] = $this->experience($days);
        }
        $validator = new RouteCompatibilityValidator;
        $seen = [];
        $structures = [];
        foreach (DB::table('tour_gen_run_results')->whereNotNull('tour_package_id')->cursor() as $result) {
            $snapshot = json_decode($result->source_snapshot, true);
            if (!empty($snapshot['structure'])) {
                $structures[count($snapshot['structure'])][] = $snapshot['structure'];
            }
        }
        $attempts = 0;
        $walk = function (array $path, array $next) use (&$walk, &$metrics, &$attempts, &$seen, &$existing, &$structures, $byStart, $locations, $filters, $validator, $run, $deadline): void {
            foreach ($next as $day) {
                if ($metrics['created'] >= $filters['count'] || $attempts >= 25000 || microtime(true) >= $deadline) {
                    return;
                }
                $attempts++;
                if (($path && $path[0]['tour_type'] !== $day['tour_type']) || in_array($day['id'], array_column($path, 'id'), true)) {
                    continue;
                }
                $candidate = [...$path, $day];
                $length = count($candidate);
                if ($length >= $filters['min_days']) {
                    $metrics['checked']++;
                    $nights = count(array_filter(array_column($candidate, 'overnight_location_id')));
                    $visited = collect($candidate)->flatMap(fn ($item) => [
                        $item['start_location_id'], ...array_column($item['routes'], 'to_location_id'), $item['end_location_id'],
                    ])->unique()->map(fn ($id) => $locations->get($id))->filter();
                    $destinationIds = $visited->pluck('destination_id')->filter();
                        $destination = $destinationIds->intersect($filters['destinations'] ?? [])->first() ?? $destinationIds->last();
                    $activityIds = collect($candidate)->flatMap(fn ($item) => $item['activities'])->pluck('id');
                    $categories = $this->categories($candidate);
                    $valid = $destination && (empty($filters['destinations']) || $destinationIds->intersect($filters['destinations'])->isNotEmpty())
                        && (empty($filters['country']) || $visited->contains('country_code', $filters['country']))
                        && (empty($filters['category']) || in_array((int) $filters['category'], $categories, true))
                        && (empty($filters['activity']) || $activityIds->contains((int) $filters['activity']))
                        && (empty($filters['end']) || (int) $filters['end'] === (int) $day['end_location_id'])
                        && !$validator->errors($candidate, $length, $nights)
                        && (empty($filters['complete_packages']) || $this->completeTourSource($candidate) !== []);
                    if (!$valid) {
                        $metrics['incompatible']++;
                    } else {
                        $structure = array_map(static function ($item) use ($locations) {
                            $key = fn ($id) => $locations->get($id)?->canonical_key;
                            return [
                                $key($item['start_location_id']), $key($item['end_location_id']),
                                $key($item['overnight_location_id']), $item['tour_type'],
                                array_map(fn ($leg) => [$key($leg['from_location_id']), $key($leg['to_location_id']), $leg['transport_mode']], $item['routes']),
                                array_column($item['activities'], 'canonical_key'),
                                array_map(fn ($stay) => [$stay['tier'], $stay['accommodation_id']], $item['stays']),
                            ];
                        }, $candidate);
                        $fingerprint = hash('sha256', json_encode($structure, JSON_THROW_ON_ERROR));
                        $experience = $this->experience(array_column($candidate, 'day_payload'));
                        $similar = false;
                        foreach ($structures[$length] ?? [] as $prior) {
                            $matchingDays = 0;
                            foreach ($structure as $index => $signature) {
                                if (array_slice($signature, 0, 6) === array_slice($prior[$index], 0, 6)) {
                                    $matchingDays++;
                                }
                            }
                            if ($matchingDays / $length >= ($filters['similarity'] ?? 90) / 100) {
                                $similar = true;
                                break;
                            }
                        }
                        foreach ($existing[$length] ?? [] as $prior) {
                            if ($this->similarity($experience, $prior) >= ($filters['similarity'] ?? 90) / 100) {
                                $similar = true;
                                break;
                            }
                        }
                        if ($similar || isset($seen[$fingerprint]) || DB::table('tour_gen_run_results')->where('fingerprint', $fingerprint)->exists()) {
                            $metrics['duplicates']++;
                        } elseif ($this->save($run, $candidate, $structure, $fingerprint, (int) $destination, $categories, $locations)) {
                            $seen[$fingerprint] = true;
                            $existing[$length][] = $experience;
                            $structures[$length][] = $structure;
                            $metrics['created']++;
                        } else {
                            $metrics['duplicates']++;
                        }
                    }
                }
                if ($length < $filters['max_days'] && $day['position_rule'] !== 'last') {
                    $walk($candidate, $byStart[$day['end_location_id']] ?? []);
                }
            }
        };
        $starts = !empty($filters['start']) ? [$byStart[$filters['start']] ?? []] : $byStart;
        foreach ($starts as $group) {
            $walk([], $group);
        }
        $metrics['limit_reached'] = $attempts >= 25000 || microtime(true) >= $deadline;
        $metrics['blocks'] = $blocks->count();
        $metrics['seconds'] = round(microtime(true) - $started, 4);
        DB::table('tour_gen_generation_runs')->where('id', $run)->update([
            'status' => 'complete', 'metrics' => json_encode($metrics), 'updated_at' => now(),
        ]);
    }

    private function experience(array $days): array
    {
        return array_map(static function ($day) {
            $value = ($day['location'] ?? '').' '.($day['details'] ?? $day['html'] ?? $day['title'] ?? '');
            $words = preg_split('/[^\pL\pN]+/u', mb_strtolower(strip_tags($value)), -1, PREG_SPLIT_NO_EMPTY);
            return array_unique($words ?: []);
        }, $days);
    }

    private function similarity(array $left, array $right): float
    {
        if (!$left || count($left) !== count($right)) {
            return 0;
        }
        $total = 0;
        foreach ($left as $index => $words) {
            $total += count(array_intersect($words, $right[$index])) / max(1, count(array_unique([...$words, ...$right[$index]])));
        }
        return $total / count($left);
    }

    private function categories(array $days): array
    {
        return collect($days)->flatMap(fn ($day) => $day['day_payload']['category_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    private function save(int $run, array $candidate, array $structure, string $fingerprint, int $destinationId, array $categories, $locations): bool
    {
        $copied = [];
        try {
            return DB::transaction(function () use ($run, $candidate, $structure, $fingerprint, $destinationId, $categories, $locations, &$copied) {
                $result = DB::table('tour_gen_run_results')->insertOrIgnore([
                    'run_id' => $run, 'fingerprint' => $fingerprint,
                    'source_snapshot' => json_encode(['structure' => $structure, 'days' => array_map(fn ($day) => [
                        'block' => $day['id'], 'record' => $day['source_record_id'],
                        'tour' => $day['source_tour_package_id'],
                        'routes' => array_column($day['routes'], 'id'), 'stays' => array_column($day['stays'], 'id'),
                    ], $candidate)], JSON_THROW_ON_ERROR),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                if (!$result) {
                    return false;
                }
                $destination = Destination::findOrFail($destinationId);
                $days = SafariDays::prepare(array_column($candidate, 'day_payload'));
                foreach ($days as $index => &$day) {
                    foreach (array_slice($candidate[$index]['day_payload']['images'] ?? [], 0, 5) as $path) {
                        if (str_starts_with($path, 'uploads/') && is_file(public_path($path))) {
                            $day['images'][] = $copied[] = PublicImage::copy($path, 'itinerary');
                        }
                    }
                }
                unset($day);
                $source = $this->completeTourSource($candidate);
                $title = ($source['tour_title'] ?? '') ?: Str::limit(count($days).'-Day '.$destination->name.' '.Str::headline($candidate[0]['tour_type']), 180, '');
                $overview = trim(implode("\n\n", array_filter([$source['overview'] ?? '', $source['highlights'] ?? ''])));
                $tour = TourPackage::create([
                    'title' => $title, 'slug' => Str::limit(Str::slug($title), 142, '').'-'.Str::uuid(),
                    'destination_id' => $destination->id,
                    'starting_point' => $locations[$candidate[0]['start_location_id']]->name,
                    'ending_point' => $locations[end($candidate)['end_location_id']]->name,
                    'tour_type' => $candidate[0]['tour_type'], 'duration_days' => count($days),
                    'duration_nights' => count(array_filter(array_column($candidate, 'overnight_location_id'))),
                    'description' => $overview ?: Str::limit(implode("\n\n", array_unique(array_filter(array_column($days, 'details')))), 30000, ''),
                    'inclusions' => $source['inclusions'] ?? null, 'exclusions' => $source['exclusions'] ?? null,
                    'itinerary_days' => $days, 'status' => 'draft', 'currency' => 'USD',
                    'image_path' => isset($days[0]['images'][0]) ? ($copied[] = PublicImage::copy($days[0]['images'][0], 'tours')) : null,
                ]);
                $tour->categories()->sync(DB::table('tour_categories')->whereIn('id', $categories)->where('is_active', true)->pluck('id')->all());
                foreach ($candidate as $index => $block) {
                    foreach ($block['stays'] as $stay) {
                        $tour->accommodations()->attach($stay['accommodation_id'], ['day_number' => $index + 1, 'tier' => $stay['tier']]);
                    }
                }
                DB::table('tour_gen_run_results')->where('fingerprint', $fingerprint)->update(['tour_package_id' => $tour->id]);
                return true;
            });
        } catch (Throwable $error) {
            foreach ($copied as $path) {
                PublicImage::delete($path);
            }
            throw $error;
        }
    }

    private function completeTourSource(array $candidate): array
    {
        $first = $candidate[0];
        $package = $first['day_payload']['package'] ?? [];
        $reference = $package['tour_ref'] ?? '';
        if (!$reference || !$first['source_record_id']) {
            return [];
        }
        $batch = DB::table('tour_gen_import_records')->where('id', $first['source_record_id'])->value('batch_id');
        $recordIds = DB::table('tour_gen_import_records')->where('batch_id', $batch)->where('status', 'approved')
            ->get(['id', 'mapped_payload'])->filter(fn ($record) => (json_decode($record->mapped_payload, true)['tour_ref'] ?? '') === $reference)
            ->pluck('id')->all();
        $selected = array_column($candidate, 'source_record_id');
        if (count($selected) !== count($recordIds) || array_diff($selected, $recordIds)) {
            return [];
        }
        foreach ($candidate as $index => $day) {
            if ((int) ($day['day_payload']['package']['day_number'] ?? 0) !== $index + 1) {
                return [];
            }
        }
        return $package;
    }
}
