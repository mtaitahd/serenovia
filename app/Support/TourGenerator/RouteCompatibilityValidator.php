<?php

namespace App\Support\TourGenerator;

final class RouteCompatibilityValidator
{
    /**
     * Validate ordered library records with their resolved routes, activities and stays.
     * An end location change must be accounted for by route legs inside that day.
     */
    public function errors(array $days, int $duration, int $nights, array $context = []): array
    {
        if ($duration < 1 || count($days) !== $duration) {
            return ['The itinerary must contain one block for each tour day.'];
        }

        $errors = [];
        $previousEnd = $context['start_location_id'] ?? null;
        $overnights = 0;

        foreach (array_values($days) as $index => $day) {
            $number = $index + 1;
            $start = $day['start_location_id'];
            $end = $day['end_location_id'];
            $overnight = $day['overnight_location_id'] ?? null;
            $itinerary = $day['day_payload'];
            $position = $day['position_rule'];
            $require = static function (bool $condition, string $message) use (&$errors, $number): void {
                if (!$condition) {
                    $errors[] = "Day {$number}: {$message}";
                }
            };

            $require($day['status'] === 'approved', 'the day block is awaiting approval.');
            $require(filled($itinerary['title'] ?? null) || filled($itinerary['details'] ?? null), 'the block has no itinerary content.');
            $require($previousEnd === null || $previousEnd === $start, 'the start does not match the previous stop.');
            $require(match ($position) {
                'any' => true,
                'first' => $number === 1,
                'middle' => $number > 1 && $number < $duration,
                'last' => $number === $duration,
                default => false,
            }, 'this block is not eligible for this position.');

            $cursor = $start;
            $visited = [$start];
            foreach ($day['routes'] ?? [] as $route) {
                $require($route['status'] === 'approved' && $this->applies($route['constraints'] ?? [], $context), 'a route leg is unavailable under the selected conditions.');
                $require($route['from_location_id'] === $cursor, 'the route legs do not connect in the approved direction.');
                $cursor = $route['to_location_id'];
                $visited[] = $cursor;
            }
            $require($cursor === $end, 'the approved route legs do not reach the end location.');

            foreach ($day['activities'] ?? [] as $activity) {
                $require($activity['status'] === 'approved' && in_array($activity['location_id'], $visited, true), 'an activity is not approved at a stop on this route.');
            }

            $stays = $day['stays'] ?? [];
            if ($overnight !== null) {
                $overnights++;
                $require($overnight === $end, 'the overnight stop must match the end location.');
                $require($stays !== [], 'the overnight stop needs an approved accommodation.');
            }
            foreach ($stays as $stay) {
                $require(
                    $overnight !== null && $stay['location_id'] === $overnight
                        && $stay['status'] === 'approved' && $this->applies($stay['constraints'] ?? [], $context),
                    'an accommodation is incompatible with the overnight stop.',
                );
            }
            $previousEnd = $end;
        }

        if ($overnights !== $nights) {
            $errors[] = 'The number of overnight stops does not match the tour duration.';
        }
        if (isset($context['end_location_id']) && $previousEnd !== $context['end_location_id']) {
            $errors[] = 'The final stop does not match the requested end location.';
        }

        return array_values(array_unique($errors));
    }

    private function applies(array $constraints, array $context): bool
    {
        foreach ($constraints as $key => $allowedValues) {
            if (!is_array($allowedValues) || !array_key_exists($key, $context) || !in_array($context[$key], $allowedValues, true)) {
                return false;
            }
        }

        return true;
    }
}
