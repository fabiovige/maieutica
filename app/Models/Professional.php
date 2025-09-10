<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'bio',
        'specialty_id',
        'created_by',
        'updated_by',
        'deleted_by',
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
        return $this->belongsToMany(Kid::class, 'kid_professional');
    }
}
