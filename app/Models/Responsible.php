<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsible extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'user_id', 'name', 'email', 'cell', 'created_by', 'updated_by', 'deleted_by'];

    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}