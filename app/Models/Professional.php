<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_number',
        'bio',
        'specialty_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_professional');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_professional')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }
}
