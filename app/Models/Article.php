<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use \App\Models\Concerns\StablePublicSlug;

    public function scopePubliclyVisible($query)
    {
        $query->where('status', 'published');
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body', 'image_path',
        'is_featured', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'published_at' => 'datetime'];
    }
}
