<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalCard extends Model
{
    protected $fillable = [
        'name',
        'job_title',
        'email',
        'phone',
        'website',
        'linkedin_url',
        'github_url',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], ['name' => config('app.name')]);
    }
}
