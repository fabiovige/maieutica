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
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'allow' => 'boolean',
    ];

    public const SUPERADMIN = 1;
    //public const ADMIN = 2;
    //public const ROLE_PAIS = 3;
    //public const ROLE_PROFESSION = 4;

    // Constantes para os tipos
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        if ($this->role) {
            return $this->role->id == 1;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if ($this->role) {
            return $this->role->id == 2;
        }

        return false;
    }

    public function isProfessional(): bool
    {
        if ($this->role) {
            return $this->role->id == self::ROLE_PROFESSION;
        }

        return false;
    }

    public function isPais(): bool
    {
        if ($this->role) {
            return $this->role->id == self::ROLE_PAIS;
        }

        return false;
    }

    public function scopeGetUsers()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            return self::with('role')->where([
                ['role_id', '!=', 1],
            ])->orWhere('created_by', '=', auth()->user()->id);
        } else {
            return self::where('created_by', '=', auth()->user()->id);
        }
    }

    public static function scopeListUsers()
    {
        return self::where([
            ['role_id', '!=', 1],
            ['role_id', '!=', 2],
            ['role_id', '!=', 3],
        ])->get();
    }

    public static function listAssocUser($type)
    {
        if (auth()->user()->isSuperAdmin()) {
            return self::where('type', '=', $type)->get();
        } elseif (auth()->user()->isAdmin()) {
            return self::where('type', '=', $type)
                ->where('created_by', '!=', 1)->get();
        } else {
            return self::where('type', '=', $type)
                ->where('created_by', '=', auth()->user()->id)->get();
        }
    }
}
