<?php

namespace App\Models;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Site extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'url',
        'key',
        'value',
        'status',
        'details',
        'error',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'details' => 'array',
        'status' => SiteStatus::class,
    ];

    use HasFactory;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
        // Chain fluent methods for configuration options
    }
}
