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
}
