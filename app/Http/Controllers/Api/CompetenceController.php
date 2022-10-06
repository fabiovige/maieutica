<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetenceResource;
use App\Models\Competence;
use Illuminate\Http\Request;

class CompetenceController extends Controller
{
    public function index(Request $request)
    {
        $competences = Competence::checklistCompetences($request->checklist_id, $request->level, $request->domain);
        return CompetenceResource::collection($competences);
    }
}
