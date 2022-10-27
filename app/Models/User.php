<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'allow',
        'created_by',
        'updated_by',
        'deleted_by',
        'responsible_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'allow' => 'boolean'
    ];

    const SUPERADMIN = 1;

    const TYPE_E = 'e';
    const TYPE_I = 'i';

    const TYPE = [
        'i' => 'Interno',
        'e' => 'Externo'
    ];

    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    public function responsible()
    {
        return $this->hasMany(Responsible::class);
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

    public function scopeListUsers()
    {
        return self::select('id', 'name')->where([
            ['id', '!=', 1],
            ['id', '!=', 2],
        ])->get()->toArray();
    }

    public static function listAssocUser($type) {

        if (auth()->user()->isSuperAdmin()) {
            return self::where('type', '=', $type)->get();
        } else if (auth()->user()->isAdmin()) {
            return self::where('type', '=', $type)
            ->where('created_by', '!=', 1)->get();
        } else {
            return self::where('type', '=', $type)
            ->where('created_by', '=', auth()->user()->id)->get();
        }
    }

}
