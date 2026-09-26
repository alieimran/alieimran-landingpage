<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SharePage extends Model
{
    public const MAX_IMAGES = 5;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'target_url',
        'button_label',
        'images',
        'preview_image_url',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'enabled' => 'boolean',
        ];
    }

    /**
     * Public URLs of the images to show on the page: the admin's own
     * uploads when there are any, otherwise the cover image scraped
     * from the target link (e.g. a Google Photos album's og:image).
     *
     * @return list<string>
     */
    public function displayImages(): array
    {
        $uploaded = array_map(fn (string $path) => Storage::disk('public')->url($path), $this->images ?? []);

        if ($uploaded !== []) {
            return $uploaded;
        }

        return $this->preview_image_url ? [$this->preview_image_url] : [];
    }
}
