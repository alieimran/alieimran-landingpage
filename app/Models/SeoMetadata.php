<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'page_key',
        'title',
        'description',
        'canonical_url',
        'og_image',
        'twitter_card',
        'robots',
    ];

    public static function forPage(string $pageKey): ?self
    {
        return self::query()->where('page_key', $pageKey)->first();
    }
}
