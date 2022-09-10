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

    public function index(Request $request)
    {
        $checklist = Checklist::findOrFail($request->checklist_id);
        $checklist->competences()->sync([
            44 => ['note' => 4],
            45 => ['note' => 5],
            46 => ['note' => 6],
            47 => ['note' => 7]
        ]);

        //return ChecklistCompetenceResource::collection($checklist->get());
    }

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

        dd($checklist->competences()->sync($notes));

        dd($notes);
    }

    protected function register($checklist_id, $competence_id, $note)
    {
        $data = [
            'checklist_id' => $checklist_id,
            'competence_id' => $competence_id,
            'note' => $note
        ];

        $checklist = Checklist::where('checklist_id', $checklist_id)->where('competence_id', $competence_id);
        if($checklist->count()) {
            $checklist->update($data);
        } else {
            ChecklistCompetence::create($data);
        }
        return true;
    }

//    public function checklistRegister(Request $request)
//    {
//        $checklist_id = $request->checklist_id;
//        $competence_description_id = $request->competence_description_id;
//        $c = ChecklistRegister::getChecklistRegister($checklist_id, $competence_description_id);
//        return ChecklistRegisterResource::collection($c);
//    }
}
