<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function professionalProfiles()
    {
        return $this->hasMany(ProfessionalProfile::class);
    }

    public function professionals()
    {
        return $this->hasManyThrough(
            User::class,
            ProfessionalProfile::class,
            'specialty_id', // Chave em professional_profiles
            'id', // Chave em users
            'id', // Chave local em specialties
            'user_id' // Chave em professional_profiles
        );
    }
}
