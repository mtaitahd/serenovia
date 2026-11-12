<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'tour_package_id', 'travel_date', 'travellers', 'message', 'status',
    ];

    protected function casts(): array
    {
        return ['travel_date' => 'date'];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }
}
