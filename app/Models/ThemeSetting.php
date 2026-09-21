<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $fillable = [
        'primary_color',
        'secondary_color',
        'background_color',
        'text_color',
        'button_style',
        'border_radius',
        'font_family',
        'logo_path',
        'favicon_path',
        'background_image',
        'dark_mode_enabled',
    ];

    protected function casts(): array
    {
        return [
            'dark_mode_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1]);
    }
}
