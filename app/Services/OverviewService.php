<?php

namespace App\Services;

use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Kid;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class OverviewService
{
    public function getOverviewData($kidId, $levelId = null, $checklistId = null)
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter o checklist atual (mais recente)
        $currentChecklist = Checklist::where('kid_id', $kidId)
            ->orderBy('id', 'desc')
            ->first();

        if (! $currentChecklist) {
            throw new Exception('Nenhum checklist encontrado!');
        }

        // Obter o checklist de comparação, se um ID foi fornecido
        $previousChecklist = $checklistId ? Checklist::find($checklistId) : null;

        // Obter todos os checklists para o combobox, excluindo o atual
        $allChecklists = Checklist::where('kid_id', $kidId)
            ->where('id', '<>', $currentChecklist->id)
            ->orderBy('id', 'desc')
            ->get();

        // Obter os níveis disponíveis
        $levels = [];
        for ($i = 1; $i <= $currentChecklist->level; $i++) {
            $levels[] = $i;
        }

        // Obter os domínios
        $domains = $this->getDomainsByLevel($levelId);

        // Preparar os dados por domínio
        $domainData = $this->prepareDomainData($domains, $currentChecklist, $levelId);

        // Cálculo dos percentuais
        $totalItemsTested = array_sum(array_column($domainData, 'itemsTested'));
        $totalItemsValid = array_sum(array_column($domainData, 'itemsValid'));
        $totalItemsInvalid = array_sum(array_column($domainData, 'itemsInvalid'));
        $totalItemsTotal = array_sum(array_column($domainData, 'itemsTotal'));

        $totalPercentage = $totalItemsTested > 0 ? ($totalItemsValid / $totalItemsTested) * 100 : 0;
        $averagePercentage = $this->calculateAveragePercentage($domainData);

        // Calcular a idade de desenvolvimento
        $developmentalAgeInMonths = $ageInMonths * ($totalPercentage / 100);
        $delayInMonths = $ageInMonths - $developmentalAgeInMonths;

        // Identificar áreas frágeis (percentual < 100%)
        $weakAreas = array_filter($domainData, function ($domain) {
            return $domain['percentage'] < 100;
        });

        // Ordenar áreas frágeis
        usort($weakAreas, function ($a, $b) {
            return $a['percentage'] <=> $b['percentage'];
        });

        return compact(
            'kid',
            'ageInMonths',
            'domainData',
            'totalItemsTested',
            'totalItemsValid',
            'totalItemsInvalid',
            'totalItemsTotal',
            'totalPercentage',
            'developmentalAgeInMonths',
            'delayInMonths',
            'weakAreas',
            'currentChecklist',
            'previousChecklist',
            'allChecklists',
            'levelId',
            'levels',
            'domains',
            'averagePercentage'
        );
    }

    private function getDomainsByLevel($levelId)
    {
        if ($levelId) {
            // Obter os domínios para o nível selecionado
            $domainLevels = DB::table('domain_level')
                ->where('level_id', $levelId)
                ->pluck('domain_id');

            return Domain::whereIn('id', $domainLevels)->get();
        } else {
            // Obter todos os domínios
            return Domain::all();
        }
    }

    private function prepareDomainData($domains, $currentChecklist, $levelId)
    {
        $domainData = [];
        foreach ($domains as $domain) {
            // Obter as competências do domínio
            $competences = Competence::where('domain_id', $domain->id)
                ->when($levelId, function ($query, $levelId) {
                    return $query->where('level_id', $levelId);
                })
                ->get();

            $itemsTotal = $competences->count();
            $itemsValid = 0;
            $currentEvaluations = DB::table('checklist_competence')
                ->where('checklist_id', $currentChecklist->id)
                ->where('note', '<>', 0)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->select('competence_id', 'note')
                ->get()
                ->keyBy('competence_id');

            $itemsTested = $currentEvaluations->count();
            foreach ($competences as $competence) {
                $evaluation = $currentEvaluations->get($competence->id);
                if ($evaluation && $evaluation->note == 3) {
                    $itemsValid++;
                }
            }

            $percentage = $itemsTested > 0 ? ($itemsValid / $itemsTested) * 100 : 0;
            $itemsInvalid = $itemsTested - $itemsValid;

            $domainData[] = [
                'code' => $domain->id,
                'name' => $domain->name,
                'initial' => $domain->initial,
                'itemsTested' => $itemsTested,
                'itemsValid' => $itemsValid,
                'itemsInvalid' => $itemsInvalid,
                'itemsTotal' => $itemsTotal,
                'percentage' => round($percentage, 2),
            ];
        }

        return $domainData;
    }

    private function calculateAveragePercentage($domainData)
    {
        $totalPercentageGeral = 0;
        $totalDomains = count($domainData);
        foreach ($domainData as $domain) {
            $percentage = $domain['itemsTested'] > 0 ? ($domain['itemsValid'] / $domain['itemsTested']) * 100 : 0;
            $totalPercentageGeral += $percentage;
        }

        return round($totalDomains > 0 ? $totalPercentageGeral / $totalDomains : 0, 2);
    }
}
