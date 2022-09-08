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
        'created_by', 'updated_by', 'deleted_by',
        'responsible_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function kids(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kid::class);
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function responsible(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Responsible::class);
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
}
