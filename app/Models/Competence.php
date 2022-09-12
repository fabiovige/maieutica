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

    public static function total($level_id)
    {
        return DB::select("SELECT COUNT(1) AS total FROM competences AS c left JOIN checklist_competence AS cc ON cc.competence_id = c.id WHERE c.level_id = $level_id");
    }

    public static function partial($level_id)
    {
        return DB::select("SELECT COUNT(1) AS partial FROM competences AS c inner JOIN checklist_competence AS cc ON cc.competence_id = c.id WHERE c.level_id = $level_id");
    }
}
