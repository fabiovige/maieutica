<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Checklist extends Model
{
    use HasFactory, SoftDeletes;

    const LEVEL = [
        '1' => 'Nível 1',
        '2' => 'Nível 2',
        '3' => 'Nível 3',
        '4' => 'Nível 4',
    ];

    const SITUATION = [
        'a' => 'Aberto',
        'f' => 'Fechado'
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

    public static function calculatePercentage($request)
    {
        $queryBuilder = DB::table('checklist_competence AS cc')
            ->select('cc.checklist_id', 'c.level_id', 'c.domain_id', 'cc.competence_id', 'cc.note', 'd.name', 'd.initial', 'd.color')
            ->leftJoin('competences AS c','c.id','=','cc.competence_id')
            ->leftJoin('domains AS d','d.id','=','c.domain_id')
            ->leftJoin('checklists AS ch','ch.id','=','cc.checklist_id')
            ->where('cc.checklist_id','=',$request['checklist_id']);

        if(isset($request['level_id'])) {
            $queryBuilder->where('c.level_id', '=', $request['level_id']);
        }

        return $queryBuilder->get();
    }

}
