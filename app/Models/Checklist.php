<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasLogging;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Checklist extends Model
{
    use HasFactory;
    use HasLogging;
    use HasAuditLog;
    use SoftDeletes;

    public const LEVEL = [
        '1' => 'Nível 1',
        '2' => 'Nível 2',
        '3' => 'Nível 3',
        '4' => 'Nível 4',
    ];

    public const SITUATION = [
        'a' => 'Aberto',
        'f' => 'Fechado',
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y',
    ];

    protected $fillable = ['level', 'kid_id', 'situation', 'description', 'created_by', 'updated_by', 'deleted_by', 'created_at'];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class)->withPivot('note');
    }

    public function planes(): HasMany
    {
        return $this->hasMany(Plane::class);  // Um checklist pode ter vários planes
    }

    public function getSituationLabelAttribute()
    {
        return self::SITUATION[$this->situation] ?? 'Desconhecido';
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($checklist) {
            // Se o checklist for retroativo (data diferente de hoje), não fecha os outros e mantém fechado
            if (isset($checklist->created_at) && !Carbon::parse($checklist->created_at)->isToday()) {
                $checklist->situation = 'f';

                return;
            }
            // Se for de hoje, fecha os anteriores e mantém aberto
            Checklist::where('kid_id', $checklist->kid_id)
                ->where('situation', 'a') // Apenas se estiver aberto
                ->update(['situation' => 'f']);
            $checklist->situation = 'a';
        });
    }

    public static function calculatePercentage($request)
    {
        $queryBuilder = DB::table('checklist_competence AS cc')
            ->select('cc.checklist_id', 'c.level_id', 'c.domain_id', 'cc.competence_id', 'cc.note', 'd.name', 'd.initial', 'd.color')
            ->leftJoin('competences AS c', 'c.id', '=', 'cc.competence_id')
            ->leftJoin('domains AS d', 'd.id', '=', 'c.domain_id')
            ->leftJoin('checklists AS ch', 'ch.id', '=', 'cc.checklist_id')
            ->where('cc.checklist_id', '=', $request['checklist_id']);

        if (isset($request['level_id'])) {
            $queryBuilder->where('c.level_id', '=', $request['level_id']);
        }

        return $queryBuilder->get();
    }


    public function getStatusAvaliation($checklistId)
    {
        $notes = collect([0, 1, 2, 3])->map(function ($note) {
            return (object)[
                'note' => $note,
                'total_competences' => 0,
                'note_description' => match ($note) {
                    0 => 'Não observado',
                    1 => 'Em desenvolvimento',
                    2 => 'Não desenvolvido',
                    3 => 'Desenvolvido'
                },
            ];
        });

        $results = DB::table('checklist_competence')
            ->select(
                DB::raw('COUNT(competence_id) as total_competences'),
                'note'
            )
            ->where('checklist_id', $checklistId)
            ->groupBy('note')
            ->get();

        return $notes->map(function ($note) use ($results) {
            $result = $results->firstWhere('note', $note->note);
            if ($result) {
                $note->total_competences = $result->total_competences;
            }

            return $note;
        });
    }

    public static function getCompetencesByNote($checklistId, $note)
    {
        return DB::table('checklist_competence as cc')
            ->join('competences as c', 'c.id', '=', 'cc.competence_id')
            ->join('domains as d', 'd.id', '=', 'c.domain_id')
            ->select('c.id', 'd.name as domain_name', 'c.level_id', 'c.description')
            ->where('cc.checklist_id', $checklistId)
            ->where('cc.note', $note)
            ->get();
    }
}
