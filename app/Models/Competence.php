<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'initial'];

    public function competenceDescriptions()
    {
        return $this->hasMany(CompetenceDescription::class);
    }

    public static function competencesByLevel($level = 1)
    {
        return DB::table('competence_descriptions as cd')
            ->join('competences as c', 'cd.competence_id', '=', 'c.id')
            ->select('c.id', 'c.name')
            ->where('cd.level', '=', $level)
            ->groupBy('cd.competence_id')
            ->orderBy('c.name')
            ->get();
    }

    public static function competenceDescriptionsByLevel($level = 1, $competence_id = 2)
    {
        return DB::table('competence_descriptions as cd')
            ->join('competences as c', 'cd.competence_id', '=', 'c.id')
            ->select('cd.id', 'cd.code', 'cd.description')
            ->where('cd.level', '=', $level)
            ->where('cd.competence_id', '=', $competence_id)
            ->orderBy('cd.code')
            ->get();
    }
}
