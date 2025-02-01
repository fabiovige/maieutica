<?php

namespace App\Services;

use App\Interfaces\Services\KidServiceInterface;
use App\Interfaces\Repositories\KidRepositoryInterface;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Plane;
use App\Util\MyPdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class KidService implements KidServiceInterface
{
    protected $kidRepository;

    public function __construct(KidRepositoryInterface $kidRepository)
    {
        $this->kidRepository = $kidRepository;
    }

    public function findById($id)
    {
        return $this->kidRepository->findById($id);
    }

    public function getKids()
    {
        return $this->kidRepository->getKids();
    }

    public function getKidDetails($kidId)
    {
        $kid = $this->kidRepository->findById($kidId);
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        return [
            'kid' => $kid,
            'ageInMonths' => $ageInMonths,
            'currentChecklist' => $this->kidRepository->getCurrentChecklist($kidId)
        ];
    }

    public function createKid(array $data)
    {
        return DB::transaction(function () use ($data) {
            $kidData = [
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'created_by' => auth()->user()->id,
            ];

            $kid = $this->kidRepository->create($kidData);

            // Se o usuário atual é um profissional, associá-lo como profissional primário
            if (Auth::user()->hasRole('professional')) {
                $kid->addProfessional(Auth::user()->id, true);
            }

            // Se foram fornecidos outros profissionais
            if (!empty($data['professionals'])) {
                $kid->assignProfessionals(
                    $data['professionals'],
                    $data['primary_professional_id'] ?? null
                );
            }

            return $kid;
        });
    }

    public function updateKid($kid, array $data)
    {
        return DB::transaction(function () use ($kid, $data) {
            $this->kidRepository->update($kid, $data);

            if (isset($data['professionals'])) {
                $kid->assignProfessionals(
                    $data['professionals'],
                    $data['primary_professional_id'] ?? null
                );
            }

            return $kid;
        });
    }

    public function deleteKid($kid)
    {
        return $this->kidRepository->delete($kid);
    }

    public function generatePdfPlane($planeId)
    {
        try {
            $plane = Plane::findOrFail($planeId);
            $kid = $this->findById($plane->kid_id);
            $nameKid = $kid->name;
            $therapist = $kid->professional->name;
            $date = $plane->first()->created_at;
            $arr = [];

            foreach ($plane->competences()->get() as $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $this->setPdfPreferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'), $plane->name);

            $this->addCompetencesToPdf($pdf, $arr);

            return $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            throw new Exception('Erro ao gerar PDF do plano: ' . $e->getMessage());
        }
    }

    public function generatePdfPlaneAuto($kidId, $checklistId, $note)
    {
        try {
            $kid = $this->findById($kidId);
            $checklist = Checklist::findOrFail($checklistId);

            if ($checklist->kid_id != $kidId) {
                throw new Exception('Este checklist não pertence a esta criança.');
            }

            $plane = $this->getOrCreatePlane($kid, $checklist, $note);
            $competencesNotes = $this->getCompetencesForNote($checklist, $note);

            if (empty($competencesNotes)) {
                throw new Exception('Não existem competências para este checklist e nota.');
            }

            $plane->competences()->sync($competencesNotes);

            return $this->generatePlanePdf($plane, $kid);
        } catch (Exception $e) {
            throw new Exception('Erro ao gerar PDF do plano automático: ' . $e->getMessage());
        }
    }

    private function setPdfPreferences(&$pdf, $kid, $therapist, $planeId, $date, $planeName = null)
    {
        $preferences = [
            'HideToolbar' => true,
            'HideMenubar' => true,
            'HideWindowUI' => true,
            'FitWindow' => true,
            'CenterWindow' => true,
            'DisplayDocTitle' => true,
            'NonFullScreenPageMode' => 'UseNone',
            'ViewArea' => 'CropBox',
            'ViewClip' => 'CropBox',
            'PrintArea' => 'CropBox',
            'PrintClip' => 'CropBox',
            'PrintScaling' => 'AppDefault',
            'Duplex' => 'DuplexFlipLongEdge',
            'PickTrayByPDFSize' => true,
            'PrintPageRange' => [1, 1, 2, 3],
            'NumCopies' => 2,
        ];

        $pdf->setViewerPreferences($preferences);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 18);
        $pdf->Cell(0, 60, 'PLANO DE INTERVENÇÃO N.: ' . $planeId, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 14);
        $pdf->Write(0, 'Profissional: ' . $therapist, '', 0, 'C', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, 'Data: ' . $date, '', 0, 'C', true);

        $pdf->Ln(15);
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Write(0, $kid->name, '', 0, 'C', true);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, $kid->FullNameMonths, '', 0, 'C', true);
        $pdf->Ln(3);

        if ($planeName) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, '(' . $planeName . ')', '', 0, 'C', true);
            $pdf->Ln(3);
        }
    }

    private function addCompetencesToPdf(&$pdf, $arr)
    {
        foreach ($arr as $initial => $v) {
            $countCompetences = 1;
            $pdf->AddPage();

            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 14);

            $domain = $v['domain']->name;
            $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

            foreach ($v['competences'] as $competence) {
                if ($countCompetences == 8) {
                    $pdf->AddPage();
                    $countCompetences = 1;
                }
                $countCompetences++;

                $this->addCompetenceToPdf($pdf, $competence, $v['domain']);
            }
        }
    }

    private function addCompetenceToPdf(&$pdf, $competence, $domain)
    {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $txt = $competence->level_id . $domain->initial . $competence->code . ' - ' . $competence->description;
        $pdf->Ln(5);
        $pdf->Write(0, $txt, '', 0, 'L', true);

        $pdf->Ln(1);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Write(0, '"' . $competence->description_detail . '"', '', 0, 'L', true);

        $pdf->Ln(4);
        $pdf->SetFont('helvetica', '', 9);
        $etapas = 'Etapa 1.:_____        Etapa 2.:_____       Etapa 3.:_____       Etapa 4.:_____       Etapa 5.:_____';
        $pdf->Write(0, $etapas, '', 0, 'L', true);
    }

    private function getOrCreatePlane($kid, $checklist, $note)
    {
        $dataCreatePlane = [
            'kid_id' => $kid->id,
            'name' => Plane::NOTES_DESCRIPTION[$note],
            'checklist_id' => $checklist->id,
            'created_by' => auth()->user()->id,
        ];

        return Plane::firstOrCreate(
            [
                'kid_id' => $kid->id,
                'checklist_id' => $checklist->id,
                'is_active' => true,
                'name' => $dataCreatePlane['name']
            ],
            $dataCreatePlane
        );
    }

    private function getCompetencesForNote($checklist, $note)
    {
        return Checklist::getCompetencesByNote($checklist->id, $note)->pluck('id')->toArray();
    }

    private function generatePlanePdf($plane, $kid)
    {
        $nameKid = $kid->name;
        $therapist = $kid->professional->name;
        $date = $plane->first()->created_at;
        $arr = [];

        $competences = $plane->competences()->get();
        foreach ($competences as $competence) {
            $initial = $competence->domain()->first()->initial;
            $arr[$initial]['domain'] = $competence->domain()->first();
            $arr[$initial]['competences'][] = $competence;
        }

        $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->setPdfPreferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'), $plane->name);
        $this->addCompetencesToPdf($pdf, $arr);

        return $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
    }

    public function getRadarChartData($kidId, $levelId, $checklistId = null)
    {
        $kid = $this->findById($kidId);
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        if ($levelId == 0) {
            $levelId = [1, 2, 3, 4];
        } else {
            $levelId = [$levelId];
        }

        $domainLevels = DB::table('domain_level')
            ->whereIn('level_id', $levelId)
            ->pluck('domain_id');

        $domains = Domain::whereIn('id', $domainLevels)->get();
        $currentChecklist = $this->kidRepository->getCurrentChecklist($kidId);

        if (!$currentChecklist) {
            throw new Exception('Nenhum checklist encontrado!');
        }

        // Implementar lógica de dados do radar chart
        // ... código do radar chart ...

        return [
            'kid' => $kid,
            'ageInMonths' => $ageInMonths,
            'domains' => $domains,
            'currentChecklist' => $currentChecklist,
            // ... outros dados necessários
        ];
    }

    public function getDomainDetailsData($kidId, $levelId, $domainId, $checklistId = null)
    {
        $kid = $this->findById($kidId);
        $domain = Domain::findOrFail($domainId);
        $currentChecklist = $this->kidRepository->getCurrentChecklist($kidId);

        if (!$currentChecklist) {
            throw new Exception('Nenhum checklist encontrado!');
        }

        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        $competences = Competence::where('domain_id', $domainId)
            ->where('level_id', $levelId)
            ->get();

        // Implementar lógica de detalhes do domínio
        // ... código dos detalhes do domínio ...

        return [
            'kid' => $kid,
            'domain' => $domain,
            'ageInMonths' => $ageInMonths,
            'competences' => $competences,
            'currentChecklist' => $currentChecklist,
            // ... outros dados necessários
        ];
    }
}
