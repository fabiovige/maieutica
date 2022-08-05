<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_by', 'updated_by', 'deleted_by'];

    public function abilities()
    {
        return $this->hasMany(Ability::class);
    }
}
