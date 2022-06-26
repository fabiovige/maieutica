<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competence extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'initial', 'created_by', 'updated_by', 'deleted_by'];

    public function competenceItems()
    {
        return $this->hasMany(CompetenceItem::class);
    }
}
