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
            if($v!=""){
                $notes[$c] = ['note' => $v];
            }
        }

        $checklist = Checklist::findOrFail($request->checklist_id);
        $checklist->competences()->syncWithoutDetaching($notes);
    }

    public function progressbar($checklist_id, $totalLevel): float
    {
        for($i=1; $i <= $totalLevel; $i++){
            $arr[$i] = $i;
        }
        $levelIn = implode(',', $arr);
        $t = Competence::total($levelIn);
        $p = Competence::partial($checklist_id, $levelIn);
        return ceil( ( $p[0]->partial * 100 ) / $t[0]->total );
    }
}
