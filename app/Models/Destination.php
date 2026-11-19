<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use \App\Models\Concerns\StablePublicSlug;

    public function scopePubliclyVisible($query)
    {
        $query->where('status', 'published');
        return $query;
    }

    protected $fillable = [
        'name', 'slug', 'country', 'tagline', 'summary', 'description',
        'image_path', 'is_featured', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function tours(): HasMany
    {
        return $this->hasMany(TourPackage::class);
    }
}
