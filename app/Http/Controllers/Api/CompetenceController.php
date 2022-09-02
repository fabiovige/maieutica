<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistRegisterResource;
use App\Http\Resources\CompetenceDescriptionResource;
use App\Http\Resources\CompetenceResource;
use App\Models\ChecklistRegister;
use App\Models\Competence;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetenceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $level = (int) (request('level') ? request('level') : 1);
        $c = Competence::competencesByLevel($level);
        return CompetenceResource::collection($c);
    }

    public function competenceDescriptions()
    {
        $level = (int) (request('level') ? request('level') : 1);
        $competence = (int) (request('competence') ? request('competence') : 1);
        $c = Competence::competenceDescriptionsByLevel($level, $competence);
        return CompetenceDescriptionResource::collection($c);
    }

    public function checklistRegister()
    {
        $checklist_id = request('checklist_id');
        $competence_description_id = request('competence_description_id');
        $c = ChecklistRegister::getChecklistRegister($checklist_id, $competence_description_id);
        return ChecklistRegisterResource::collection($c);
    }
}
