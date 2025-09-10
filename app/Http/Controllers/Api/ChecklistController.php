<?php

namespace App\Http\Controllers\Api;

use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Level;
use App\DTOs\Responses\ChecklistResponseDto;
use App\DTOs\Responses\CollectionResponseDto;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;

class ChecklistController
{
    public function __construct(
        private readonly ChecklistService $checklistService
    ) {
    }

    public function index(): JsonResponse
    {
        $checklists = Checklist::all();

        $response = CollectionResponseDto::fromCollection(
            $checklists,
            fn ($checklist) => ChecklistResponseDto::fromModel($checklist)->toMinimalArray()
        );

        return response()->json($response);
    }

    public function show($id)
    {
        $checklist = Checklist::findOrFail($id);
        $checklist->created_at->format('d/m/Y');
        $arrCompetences = [];
        $arrCompetences['checklist']['level'] = $checklist->level;
        $arrCompetences['checklist']['created_at'] = $checklist->created_at->format('d/m/Y');
        for ($level_id = 1; $level_id <= $checklist->level; $level_id++) {
            $level = Level::findOrFail($level_id);
            $domains = $level->domains()->get();
            foreach ($domains as $c => $v) {
                $domain_id = $v->id;
                $competences = Competence::checklistCompetences($checklist->id, $level_id, $domain_id);
                $arrCompetences['levels'][$level_id]['domains'][] = $v->initial;
                $arrCompetences['levels'][$level_id]['competences'][$v->initial] = $competences->toArray();
            }
        }

        return $arrCompetences;
    }

    public function getCompetencesByNote($checklistId, $note): JsonResponse
    {
        $competences = Checklist::getCompetencesByNote($checklistId, $note);

        return response()->json([
            'checklist_id' => $checklistId,
            'note' => $note,
            'competences' => $competences,
            'total' => count($competences),
        ]);
    }
}
