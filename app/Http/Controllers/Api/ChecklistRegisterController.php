<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistRegisterRequest;
use App\Http\Resources\ChecklistCompetenceResource;
use App\Http\Resources\ChecklistRegisterResource;
use App\Models\Checklist;
use App\Models\ChecklistCompetence;
use App\Models\ChecklistRegister;
use App\Models\Competence;
use Illuminate\Http\Request;

class ChecklistRegisterController extends Controller
{
    public function store(Request $request)
    {
        $arrNotes = explode(',', $request->note);
        $notes = [];
        foreach($arrNotes as $c => $v) {
            if(!empty($v)){
                $notes[$c] = ['note' => $v];
            }
        }
        $checklist = Checklist::findOrFail($request->checklist_id);
        $checklist->competences()->syncWithoutDetaching($notes);
    }

    public function progressbar($level_id): float
    {
        $total = Competence::total($level_id);
        $partial = Competence::partial($level_id);
        $perc =  ( $partial[0]->partial * 100 ) / $total[0]->total;
        return ceil($perc);
    }
}
