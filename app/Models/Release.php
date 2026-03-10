<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $fillable = [
        'version',
        'title',
        'release_date',
        'description',
        'items',
        'commits',
    ];

    protected $casts = [
        'release_date' => 'date',
        'items' => 'array',
        'commits' => 'array',
    ];
}
