<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetenceDescription extends Model
{
    use HasFactory;

    protected $fillable = ['level', 'competence_id', 'code', 'description', 'description_detail'];

    public function compoetence()
    {
        return $this->belongsTo(Competence::class);
    }
}
