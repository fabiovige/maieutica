<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistCompetence extends Model
{
    use HasFactory;

    protected $fillable = ['checklist_id', 'competence_id', 'note'];
}
