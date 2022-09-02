<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChecklistRegister extends Model
{
    use HasFactory;

    protected $fillable = ['checklist_id', 'competence_description_id', 'note'];

    public static function getChecklistRegister($checklist_id, $competence_description_id): Collection
    {
        return DB::table('checklist_registers')
            ->select('id', 'note')
            ->where('checklist_id', $checklist_id)
            ->where('competence_description_id', $competence_description_id)
            ->get();
    }
}
