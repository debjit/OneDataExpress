<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "api_endpoint",
        "publication_id",
        "api_key",
        "is_active"
    ];
}
