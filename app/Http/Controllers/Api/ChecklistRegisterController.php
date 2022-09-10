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
        $data = $request->all();
        var_dump($data);
        $arrNote = explode(',', $data['note']);
        $arr = [];
        foreach($arrNote as $c => $v) {
            if(!empty($v)){
                $arr[$c] = $v;
            }
        }
        dd($arr);
    }

    public function store_old(StoreChecklistRegisterRequest $request)
    {
        $getChecklistRegister = ChecklistRegister::where('checklist_id', $request->checklist_id)
            ->where('competence_description_id', $request->competence_description_id);

        if($getChecklistRegister->count()) {
            $id = $getChecklistRegister->first()->id;
            $checklistRegister = ChecklistRegister::findOrFail($id);
            $checklistRegister->update($request->all());
        } else {
            $checklistRegister = ChecklistRegister::create($request->all());
        }

        return new ChecklistRegisterResource($checklistRegister);
    }

//    public function checklistRegister(Request $request)
//    {
//        $checklist_id = $request->checklist_id;
//        $competence_description_id = $request->competence_description_id;
//        $c = ChecklistRegister::getChecklistRegister($checklist_id, $competence_description_id);
//        return ChecklistRegisterResource::collection($c);
//    }
}
