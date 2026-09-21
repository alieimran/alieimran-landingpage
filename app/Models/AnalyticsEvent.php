<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'event_type',
        'label',
        'url',
        'created_at',
    ];
}
