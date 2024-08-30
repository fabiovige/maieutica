<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = ['level', 'domain_id', 'code', 'description', 'description_detail'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(Checklist::class)->withPivot('note');
    }

    public function planes(): BelongsToMany
    {
        return $this->belongsToMany(Plane::class);
    }

    public static function total($level_id)
    {
        $query = "SELECT COUNT(1) AS total FROM competences AS c WHERE c.level_id in($level_id)";

        return DB::select($query);
    }

    public static function partial($checklist_id, $level_id)
    {
        $query = "SELECT COUNT(1) AS partial FROM competences AS c inner JOIN checklist_competence AS cc ON cc.competence_id = c.id WHERE cc.checklist_id = $checklist_id AND c.level_id in($level_id) AND cc.note != 0";

        return DB::select($query);
    }

    public static function checklistCompetences($checklist_id, $level_id, $domain_id)
    {
        return DB::table('checklist_competence as cc')
            ->select('c.*', 'cc.checklist_id', 'cc.note', 'd.name as domain_name')
            ->leftJoin('competences as c', 'c.id', '=', 'cc.competence_id')
            ->leftJoin('domains as d', 'd.id', '=', 'c.domain_id')
            ->where('cc.checklist_id', '=', $checklist_id)
            ->where('c.level_id', $level_id)
            ->where('c.domain_id', $domain_id)
            ->get();
    }
}
