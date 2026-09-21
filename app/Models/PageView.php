<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'path',
        'referrer',
        'device_type',
        'browser',
        'os',
        'visitor_hash',
        'created_at',
    ];
}
