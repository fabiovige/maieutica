<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'initial', 'color'];

    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }

}
