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
        'is_intern',
        'specialty_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_intern' => 'boolean',
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
            ->whereNull('kids.deleted_at');
    }

    /**
     * Users that this professional attends (as patients)
     */
    public function patients()
    {
        return $this->belongsToMany(User::class, 'professional_user_patient')
            ->whereNull('users.deleted_at')
            ->where('users.allow', 1);
    }
}
