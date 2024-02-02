<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $filable = [
        'title',
        'status',
        'body',
        'media',
        'output',
        'meta',
        'site_id',
    ];

    protected $casts = [
        'body' => 'array',
        'media' => 'array',
        'output' => 'array',
        'meta' => 'array',
    ];



    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
        // Chain fluent methods for configuration options
    }
}
