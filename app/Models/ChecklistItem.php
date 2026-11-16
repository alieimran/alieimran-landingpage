<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'title', 'url', 'note', 'is_done', 'done_at', 'sort_order'];

    protected function casts(): array
    {
        return ['is_done' => 'boolean', 'done_at' => 'datetime'];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
