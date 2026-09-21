<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteSection extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'enabled',
        'nav_visible',
        'homepage_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'nav_visible' => 'boolean',
            'homepage_visible' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeOnHomepage(Builder $query): Builder
    {
        return $query->where('enabled', true)->where('homepage_visible', true)->orderBy('sort_order');
    }

    public function scopeInNav(Builder $query): Builder
    {
        return $query->where('enabled', true)->where('nav_visible', true)->orderBy('sort_order');
    }
}
