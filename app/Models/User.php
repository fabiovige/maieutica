<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    public $temporaryPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'allow',
        'created_by',
        'updated_by',
        'deleted_by',
        'phone',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'provider_id',
        'provider_email',
        'provider_avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'allow' => 'boolean',
    ];

    // Constantes para os tipos de usuário (interno/externo)
    public const TYPE_I = 'i';

    public const TYPE_E = 'e';

    public const TYPE = [
        'i' => 'Interno',
        'e' => 'Externo',
    ];

    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    /**
     * Relação many-to-many com Professional.
     * Regra de negócio: 1 User = 1 Professional (relação 1:1 apesar do pivot).
     */

    public function professional()
    {
        return $this->belongsToMany(Professional::class, 'user_professional');
    }

    public function getSpecialtyAttribute()
    {
        return $this->professional->first()?->specialty;
    }
}
