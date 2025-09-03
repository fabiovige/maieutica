<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ChecklistService
{
    /**
     * Calcula o percentual de desenvolvimento de um checklist
     *
     * @param int $checklistId
     * @return float
     */
    public function percentualDesenvolvimento($checklistId)
    {
        // Query otimizada que calcula tudo numa só consulta
        $result = DB::table('checklist_competence')
            ->where('checklist_id', $checklistId)
            ->where('note', '<>', 0)
            ->selectRaw('
                COUNT(*) as total_tested,
                COUNT(CASE WHEN note = 3 THEN 1 END) as total_valid
            ')
            ->first();

        if ($result && $result->total_tested > 0) {
            return round(($result->total_valid / $result->total_tested) * 100, 2);
        }

        return 0;
    }

    /**
     * Calcula percentuais para múltiplos checklists de uma vez
     *
     * @param array $checklistIds
     * @return array
     */
    public function percentualDesenvolvimentoBatch(array $checklistIds): array
    {
        if (empty($checklistIds)) {
            return [];
        }

        $results = DB::table('checklist_competence')
            ->whereIn('checklist_id', $checklistIds)
            ->where('note', '<>', 0)
            ->selectRaw('
                checklist_id,
                COUNT(*) as total_tested,
                COUNT(CASE WHEN note = 3 THEN 1 END) as total_valid
            ')
            ->groupBy('checklist_id')
            ->get()
            ->keyBy('checklist_id');

        $percentages = [];
        foreach ($checklistIds as $id) {
            $result = $results->get($id);
            if ($result && $result->total_tested > 0) {
                $percentages[$id] = round(($result->total_valid / $result->total_tested) * 100, 2);
            } else {
                $percentages[$id] = 0;
            }
        }

        return $percentages;
    }
}
