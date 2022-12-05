<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Level;

class ChecklistController
{

    public function index()
    {
        $checklists = Checklist::all();
        return ChecklistResource::collection($checklists);
    }

    public function show($id)
    {
        $checklist = Checklist::findOrFail($id);
        $checklist->created_at->format('d/m/Y');
        $arrCompetences = [];
        $arrCompetences['checklist']['level'] = $checklist->level;
        $arrCompetences['checklist']['created_at'] = $checklist->created_at->format('d/m/Y');
        for($level_id=1; $level_id<=$checklist->level;$level_id++) {
            $level = Level::findOrFail($level_id);
            $domains = $level->domains()->get();
            foreach($domains as $c=>$v) {
                $domain_id = $v->id;
                $competences = Competence::checklistCompetences($checklist->id, $level_id, $domain_id);
                $arrCompetences['levels'][$level_id]['domains'][] = $v->initial;
                $arrCompetences['levels'][$level_id]['competences'][$v->initial] = $competences->toArray();
            }
        }
        return $arrCompetences;
    }

}
