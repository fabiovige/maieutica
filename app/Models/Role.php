<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'role', 'created_by', 'updated_by', 'deleted_by'];

    public const ROLE_SUPER_ADMIN = 1;

    public const ROLE_ADMIN = 2;

    public const ROLE_PAIS = 3;

    public const ROLE_PROFESSION = 4;

    public $perPage = 15;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class);
    }
}
