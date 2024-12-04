<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Checklist extends Model
{
    use HasFactory;
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

    protected $fillable = ['level', 'kid_id', 'situation', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function kid(): BelongsTo
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
            // Antes de criar o novo checklist, fechar os anteriores do mesmo kid
            Checklist::where('kid_id', $checklist->kid_id)
                ->where('situation', 'a') // Apenas se estiver aberto
                ->update(['situation' => 'f']);

            // Garantir que o novo checklist esteja com situação 'a'
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

    public static function getChecklists()
    {
        $query = Checklist::query();

        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin')) {
            // Superadmin ou admin pode ver todos os checklists e seus relacionamentos
            $query->with(['kid']);
        } else if (auth()->user()->hasRole('professional')) {
            // Profissionais podem ver checklists criados por eles ou associados a eles
            //$query->where('created_by', auth()->user()->id)
            $query->whereHas('kid', function ($q) {
                    $q->where('profession_id', auth()->user()->id);
                })
                ->with(['kid']);
        } else if (auth()->user()->hasRole('pais')) {
            // Pais podem ver checklists associados às crianças pelas quais são responsáveis
            $query->whereHas('kid', function ($q) {
                $q->where('responsible_id', auth()->user()->id);
            })
            ->with(['kid']);
        }

        return $query;
    }

    public function getStatusAvaliation($checklistId)
    {
        return DB::table('checklist_competence')
            ->select(
                DB::raw('COUNT(competence_id) as total_competences'),
                'note',
                DB::raw("CASE
                    WHEN note = 0 THEN 'Não observado'
                    WHEN note = 1 THEN 'Mais ou menos'
                    WHEN note = 2 THEN 'Difícil de obter'
                    WHEN note = 3 THEN 'Consistente'
                END as note_description")
            )
            ->where('checklist_id', $checklistId)
            ->groupBy('note')
            ->get();
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
