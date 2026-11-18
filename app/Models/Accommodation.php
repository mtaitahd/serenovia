<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accommodation extends Model
{
    protected $fillable = [
        'name', 'slug', 'destination_id', 'tier', 'location', 'description', 'amenities',
        'price_from', 'currency', 'image_path', 'gallery_photo_ids', 'is_featured', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['amenities' => 'array', 'gallery_photo_ids' => 'array', 'is_featured' => 'boolean', 'price_from' => 'decimal:2'];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'tour_package_accommodations')
            ->withPivot(['day_number', 'tier'])->withTimestamps();
    }
}
