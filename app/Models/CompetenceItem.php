<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetenceItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'level_id',
        'competence_id',
        'code',
        'description',
        'description_detail',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function competence()
    {
        return $this->belongsTo(Competence::class);
    }
}
