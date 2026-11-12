<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = [
        'path', 'source_path', 'original_size', 'optimized_size',
        'title', 'alt_text', 'category', 'sort_order',
    ];
}
