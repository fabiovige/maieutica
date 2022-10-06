<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencePlane extends Model
{
    use HasFactory;

    protected $fillable = ['plane_id', 'competence_id'];
}
