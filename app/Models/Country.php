<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Country extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'country', 'name');
    }

    public function tours(): HasManyThrough
    {
        return $this->hasManyThrough(TourPackage::class, Destination::class, 'country', 'destination_id', 'name', 'id');
    }
}
