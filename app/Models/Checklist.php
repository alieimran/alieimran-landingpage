<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Checklist extends Model
{
    protected $attributes = ['is_public' => false, 'allow_ticking' => false];

    protected $fillable = ['title', 'slug', 'is_public', 'allow_ticking', 'description', 'sort_order'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'allow_ticking' => 'boolean'];
    }

    protected static function booted(): void
    {
        // A checklist always has a link slug, even before it is shared.
        static::creating(function (Checklist $checklist) {
            $checklist->slug = $checklist->slug ?: self::generateSlug($checklist->title);
        });
    }

    /** A readable slug with a short random suffix, so a freshly shared link is not trivially guessable. */
    public static function generateSlug(string $title): string
    {
        do {
            $slug = Str::limit(Str::slug($title) ?: 'list', 40, '').'-'.Str::lower(Str::random(5));
        } while (self::where('slug', $slug)->exists());

        return $slug;
    }

    /** Visitors with the link may tick items only when the list is shared AND ticking is switched on. */
    public function visitorsCanTick(): bool
    {
        return $this->is_public && $this->allow_ticking;
    }

    public function publicUrl(): string
    {
        return route('checklists.public', $this->slug);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return array{done:int,total:int,percent:int} */
    public function progress(): array
    {
        $total = $this->items->count();
        $done = $this->items->where('is_done', true)->count();

        return ['done' => $done, 'total' => $total, 'percent' => $total ? (int) round($done / $total * 100) : 0];
    }
}
