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

        // Determinar qual checklist usar para os cálculos
        if ($checklistId) {
            // Se um checklistId foi fornecido, usar esse checklist
            $currentChecklist = Checklist::where('kid_id', $kidId)
                ->where('id', $checklistId)
                ->first();

            if (!$currentChecklist) {
                throw new Exception('Checklist selecionado não encontrado!');
            }
        } else {
            // Caso contrário, usar o checklist mais recente
            $currentChecklist = Checklist::where('kid_id', $kidId)
                ->orderBy('id', 'desc')
                ->first();
        }

        // Se não há checklist, retornar dados vazios
        if (!$currentChecklist) {
            return [
                'kid' => $kid,
                'ageInMonths' => $ageInMonths,
                'domainData' => [],
                'totalItemsTested' => 0,
                'totalItemsValid' => 0,
                'totalItemsInvalid' => 0,
                'totalItemsTotal' => 0,
                'totalPercentage' => 0,
                'developmentalAgeInMonths' => 0,
                'delayInMonths' => $ageInMonths,
                'weakAreas' => [],
                'currentChecklist' => null,
                'allChecklists' => collect([]),
                'levelId' => $levelId,
                'levels' => [],
                'domains' => collect([]),
                'checklistId' => $checklistId
            ];
        }

        // Obter todos os checklists para o combobox
        $allChecklists = Checklist::where('kid_id', $kidId)
            ->orderBy('id', 'desc')
            ->get();

        // Obter os níveis disponíveis do checklist sendo usado
        $levels = [];
        for ($i = 1; $i <= $currentChecklist->level; $i++) {
            $levels[] = $i;
        }

        // Obter os domínios
        $domains = $this->getDomainsByLevel($levelId);

        // Preparar os dados por domínio usando o checklist selecionado
        $domainData = $this->prepareDomainData($domains, $currentChecklist, $levelId);

        // Cálculo dos percentuais
        $totalItemsTested = array_sum(array_column($domainData, 'itemsTested'));
        $totalItemsValid = array_sum(array_column($domainData, 'itemsValid'));
        $totalItemsInvalid = array_sum(array_column($domainData, 'itemsInvalid'));
        $totalItemsTotal = array_sum(array_column($domainData, 'itemsTotal'));

        $totalPercentage = $totalItemsTested > 0 ? ($totalItemsValid / $totalItemsTested) * 100 : 0;

        // Calcular a idade de desenvolvimento
        $developmentalAgeInMonths = $ageInMonths * ($totalPercentage / 100);
        $delayInMonths = $ageInMonths - $developmentalAgeInMonths;

        // Identificar áreas frágeis (percentual <= 50% e com itens testados)
        $weakAreas = array_filter($domainData, function ($domain) {
            return $domain['percentage'] <= 50 && $domain['itemsTested'] > 0;
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
            'allChecklists',
            'levelId',
            'levels',
            'domains',
            'checklistId'
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

            // Obter avaliações (ignorando nota 0)
            $currentEvaluations = DB::table('checklist_competence')
                ->where('checklist_id', $currentChecklist->id)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->select('competence_id', 'note')
                ->get()
                ->keyBy('competence_id');

            // Calcular média das notas (mesma lógica do analysis)
            $sumNotes = 0;
            $countNotes = 0;
            $itemsValid = 0;

            foreach ($competences as $competence) {
                $evaluation = $currentEvaluations->get($competence->id);
                if ($evaluation && $evaluation->note !== null && $evaluation->note !== 0) {
                    $sumNotes += $evaluation->note;
                    $countNotes++;

                    if ($evaluation->note == 3) {
                        $itemsValid++;
                    }
                }
            }

            $itemsTested = $countNotes;
            $average = $countNotes > 0 ? $sumNotes / $countNotes : 0;

            // Converter média (0-3) para percentual (0-100) para manter compatibilidade
            $percentage = ($average / 3) * 100;
            $itemsInvalid = $itemsTested - $itemsValid;

            $domainData[] = [
                'code' => $domain->id,
                'name' => $domain->name,
                'initial' => $domain->initial,
                'abbreviation' => $domain->initial,
                'itemsTested' => $itemsTested,
                'itemsValid' => $itemsValid,
                'itemsInvalid' => $itemsInvalid,
                'itemsTotal' => $itemsTotal,
                'percentage' => round($percentage, 2),
            ];
        }

        return $domainData;
    }
}
