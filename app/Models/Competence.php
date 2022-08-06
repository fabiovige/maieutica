<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'initial'];

    public function competenceDescriptions()
    {
        return $this->hasMany(CompetenceDescription::class);
    }
}
