<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plane extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['kid_id', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class);
    }
}
