<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'full_name',
        'display_name',
        'job_title',
        'tagline',
        'biography',
        'profile_photo',
        'location',
        'primary_cta_label',
        'primary_cta_url',
        'secondary_cta_label',
        'secondary_cta_url',
        'portfolio_url',
        'contact_notification_email',
        'ga_tracking_id',
        'profile_visible',
    ];

    protected function casts(): array
    {
        return [
            'profile_visible' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'full_name' => config('app.name'),
            'display_name' => config('app.name'),
        ]);
    }
}
