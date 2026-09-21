<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Link extends Model
{
    protected $fillable = [
        'link_category_id',
        'title',
        'description',
        'url',
        'icon',
        'image',
        'enabled',
        'featured',
        'sort_order',
        'start_date',
        'end_date',
        'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'featured' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LinkCategory::class, 'link_category_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('enabled', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
