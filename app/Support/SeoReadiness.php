<?php
namespace App\Support;

use App\Models\TourPackage;

final class SeoReadiness
{
    public static function warnings(TourPackage $tour): array
    {
        $warnings = [];
        if (!$tour->summary && !$tour->description) $warnings[] = 'Add a short introduction or overview so travellers and search engines can understand this tour.';
        if (!$tour->itinerary && !$tour->itinerary_days) $warnings[] = 'Add the day-by-day itinerary before publishing.';
        if (!$tour->starting_point || !$tour->ending_point) $warnings[] = 'Confirm the starting and ending locations.';
        if (!$tour->inclusions || !$tour->exclusions) $warnings[] = 'Explain what is included and excluded.';
        if (!$tour->image_path) $warnings[] = 'Choose a tour cover image for the page and its social preview.';
        if (empty($tour->package_details['faqs'])) $warnings[] = 'Consider adding questions and answers specific to this tour.';
        return $warnings;
    }
}
