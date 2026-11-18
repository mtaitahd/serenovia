<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TourPackage extends Model
{
    public const PRACTICAL_FIELDS = [
        'min_group_size' => 'Minimum group size',
        'minimum_age' => 'Minimum age',
        'activity_level' => 'Activity level',
        'stay_style' => 'Accommodation style',
        'transport' => 'Transport',
        'meals' => 'Meals included',
        'guide' => 'Guide',
        'activities' => 'Activities',
        'arrival' => 'Getting there',
    ];

    protected $fillable = [
        'title', 'slug', 'destination_id', 'starting_point', 'ending_point', 'tour_type', 'duration_days', 'duration_nights',
        'price_from', 'currency', 'max_group_size', 'summary', 'description', 'itinerary', 'tour_code', 'practical_details', 'package_details',
        'itinerary_days', 'route_map_enabled', 'inclusions', 'exclusions', 'image_path', 'is_featured', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'route_map_enabled' => 'boolean',
            'itinerary_days' => 'array',
            'practical_details' => 'array',
            'package_details' => 'array',
            'published_at' => 'datetime',
            'price_from' => 'decimal:2',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TourCategory::class, 'tour_package_tour_category');
    }

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'tour_package_accommodations')
            ->withPivot(['day_number', 'tier'])->withTimestamps();
    }
}
