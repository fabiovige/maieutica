<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'registration_number',
        'bio',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
