<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\KidRepositoryInterface;
use App\Http\Requests\KidRequest;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Kid;
use App\Models\Plane;
use App\Models\User;
use App\Services\KidService;
use App\Services\OverviewService;
use App\Util\MyPdf;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role as SpatieRole;

class KidsController extends BaseController
{
    public function __construct(
        private readonly OverviewService $overviewService,
        private readonly KidService $kidService,
        private readonly KidRepositoryInterface $kidRepository
    ) {
    }

    public function index(Request $request): mixed
    {
        $this->authorize('view kids');

        return $this->handleIndexRequest(
            $request,
            fn ($filters) => $this->kidService->getPaginatedKidsForUser($filters['per_page'], $filters),
            'kids.index'
        );
    }


    public function create(): View
    {
        $this->authorize('create', Kid::class);

        $professions = $this->kidService->getProfessionalsForSelect();
        $responsibles = $this->kidService->getParentsForSelect();

        $message = label_case('Create Kids') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        return view('kids.create', compact('professions', 'responsibles'));
    }

    public function store(KidRequest $request): RedirectResponse
    {
        $this->authorize('create', Kid::class);

        try {
            $message = label_case('Store Kids ' . self::MSG_CREATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $this->kidService->createKid($request->validated());

            flash(self::MSG_CREATE_SUCCESS)->success();

            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_CREATE_ERROR)->warning();
            $message = label_case('Store Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('kids.index');
        }
    }

    public function show(Kid $kid): void
    {
    }

    public function showPlane(Kid $kid, ?int $checklistId = null): View|RedirectResponse
    {
        $this->authorize('plane manual checklist', $kid);

        try {
            Log::info('show', [
                'user' => auth()->user()->name,
                'id' => auth()->user()->id,
            ]);

            if ($kid->checklists()->count() === 0) {
                flash(self::MSG_NOT_FOUND_CHECKLIST_USER)->warning();

                return redirect()->back();
            }

            $checklists = $kid->checklists()->orderBy('created_at', 'DESC')->get();
            $kid->months = $kid->months;

            $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
            $ageInMonths = $birthdate->diffInMonths(Carbon::now());

            if ($checklistId) {
                $checklist = Checklist::findOrFail($checklistId);
            } else {
                $checklist = $checklists[0];
            }

            $data = [
                'kid' => $kid,
                'professionals' => $kid->professionals->pluck('name'),
                'checklists' => $checklists,
                'checklist' => $checklist,
                'checklist_id' => $checklists[0]->id,
                'level' => $checklists[0]->level,
                'countChecklists' => $kid->checklists()->count(),
                'countPlanes' => $kid->planes()->count(),
                'ageInMonths' => $ageInMonths,
            ];

            return view('kids.show', $data);
        } catch (Exception $e) {
            $message = label_case('Error show kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function edit(Kid $kid): View|RedirectResponse
    {
        $this->authorize('update', $kid);

        try {
            $message = label_case('Edit Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            // Verificando se os papeis existem
            $parentRole = SpatieRole::where('name', 'pais')->first();
            if (!$parentRole) {
                throw new Exception("O papel 'pais' não existe no sistema.");
            }

            $professionalRole = SpatieRole::where('name', 'professional')->first();
            if (!$professionalRole) {
                throw new Exception("O papel 'professional' não existe no sistema.");
            }

            $responsibles = $this->kidService->getParentsForSelect();
            $professions = $this->kidService->getProfessionalsForSelect();

            return view('kids.edit', compact('kid', 'responsibles', 'professions'));
        } catch (Exception $e) {
            flash($e->getMessage())->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function eyeKid(int $id): View|RedirectResponse
    {
        $kid = Kid::findOrFail($id);
        $this->authorize('view', $kid);

        try {
            $message = label_case('Edit Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            // Verificando se o papel 'pais' existe, caso contrário lançar exceção
            $parentRole = SpatieRole::where('name', 'pais')->first();
            if (!$parentRole) {
                throw new Exception("O papel 'pais' não existe no sistema.");
            }

            // Verificando se o papel 'professional' existe, caso contrário lançar exceção
            $professionalRole = SpatieRole::where('name', 'professional')->first();
            if (!$professionalRole) {
                throw new Exception("O papel 'professional' não existe no sistema.");
            }

            // Buscando usuários com o papel 'pais'
            $responsibles = User::whereHas('roles', function ($query) {
                $query->where('name', 'pais');
            })->get();

            // Buscando usuários com o papel 'professional'
            $professions = User::whereHas('roles', function ($query) {
                $query->where('name', 'professional');
            })->get();

            return view('kids.eye', compact('kid', 'responsibles', 'professions'));
        } catch (Exception $e) {
            flash($e->getMessage())->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(Request $request, Kid $kid): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date_format:d/m/Y|before:today|after:1900-01-01',
            'gender' => 'required|string',
            'ethnicity' => 'required|string',
            'professionals' => 'array',
            'professionals.*' => 'exists:professionals,id',
        ]);

        try {
            $updateData = array_merge($validated, [
                'responsible_id' => $request->input('responsible_id'),
                'primary_professional' => $request->input('primary_professional'),
            ]);

            $this->kidService->updateKid($kid->id, $updateData);

            return redirect()
                ->route('kids.index')
                ->with('success', 'Criança atualizada com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao atualizar criança: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar criança. Por favor, tente novamente.');
        }
    }

    public function destroy(Kid $kid): RedirectResponse
    {
        $this->authorize('delete', $kid);

        try {
            $message = label_case('Destroy Kids ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $this->kidService->deleteKid($kid->id);
            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('kids.index');
        } catch (Exception $e) {
            $message = label_case('Destroy Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function pdfPlane(int $id): void
    {
        try {
            $plane = Plane::findOrFail($id);
            $kid_id = $plane->kid()->first()->id;

            $kid = Kid::findOrFail($kid_id);
            $nameKid = $plane->kid()->first()->name;
            $professionals = $kid->professionals()->get();

            $professionalNames = [];
            foreach ($professionals as $professional) {
                $professionalNames[] = $professional->user->first()->name . ' - (' . $professional->specialty->name . ')';
            }
            $therapist = implode("\n", $professionalNames);


            $date = $plane->first()->created_at;
            $arr = [];

            foreach ($plane->competences()->get() as $c => $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->preferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'), $plane->name);

            $totalDomain = count($arr);
            $countDomain = 1;

            foreach ($arr as $initial => $v) {
                $countCompetences = 1;
                $pdf->AddPage();

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 14);

                // Domain
                $domain = $v['domain']->name;
                $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

                foreach ($v['competences'] as $k => $competence) {
                    if ($countCompetences == 8) {
                        $pdf->AddPage();
                        $countCompetences = 1;
                    }
                    $countCompetences++;

                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', 'B', 10);
                    $txt = $competence->level_id . $v['domain']->initial . $competence->code . ' - ' . $competence->description;
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
            }

            $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            $message = label_case('Plane Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error('Exibe Plano Erro', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash(self::MSG_NOT_FOUND)->warning();

            // return redirect()->route('kids.index');
        }
    }

    public function pdfPlaneAuto(int $kidId, int $checklistId, int $note)
    {
        $this->authorize('plane automatic checklist');

        try {
            // Primeiro, verificar se o kid existe
            $kid = Kid::findOrFail($kidId);

            // Verificar se o checklist existe
            $checklist = Checklist::findOrFail($checklistId);

            // Verificar se o checklist pertence ao kid
            if ($checklist->kid_id != $kidId) {
                throw new Exception('Este checklist não pertence a esta criança.');
            }

            // criar o plane
            $dataCreatePlane = [
                'kid_id' => $kid->id, // Usar o id do modelo encontrado
                'name' => Plane::NOTES_DESCRIPTION[$note],
                'checklist_id' => $checklist->id, // Usar o id do modelo encontrado
                'created_by' => auth()->user()->id,
            ];

            // Criar o plane dentro de uma transação

            $existingPlane = Plane::where('kid_id', $kid->id)->where('checklist_id', $checklist->id)->where('is_active', true)->where('name', $dataCreatePlane['name'])->first();
            if ($existingPlane) {
                // throw new Exception('Já existe um plano ativo para esta criança.');
                $plane = $existingPlane;
            } else {
                $plane = Plane::create($dataCreatePlane);
            }

            // get kid
            $nameKid = $kid->name;

            // get professionals
            $professionals = $kid->professionals()->get();

            $professionalNames = [];
            foreach ($professionals as $professional) {
                $professionalNames[] = $professional->user->first()->name . ' - (' . $professional->specialty->name . ')';
            }
            $therapist = implode("\n", $professionalNames);

            $date = $plane->first()->created_at;
            $arr = [];

            // get competences por nota
            $competencesNotes = Checklist::getCompetencesByNote($checklist->id, $note)->pluck('id')->toArray();

            // verifica se existe competencias
            if (count($competencesNotes) == 0) {
                throw new Exception('Não existem competências para este checklist e nota.');
            }

            // se exite competentes adiciona a competence_plane
            $plane->competences()->sync($competencesNotes);

            // get competences do plane
            $competences = $plane->competences()->get();

            foreach ($competences as $c => $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->preferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'), $plane->name);

            $totalDomain = count($arr);
            $countDomain = 1;

            foreach ($arr as $initial => $v) {
                $countCompetences = 1;
                $pdf->AddPage();

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 14);

                // Domain
                $domain = $v['domain']->name;
                $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

                foreach ($v['competences'] as $k => $competence) {
                    if ($countCompetences == 8) {
                        $pdf->AddPage();
                        $countCompetences = 1;
                    }
                    $countCompetences++;

                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', 'B', 10);
                    $txt = $competence->level_id . $v['domain']->initial . $competence->code . ' - ' . $competence->description;
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
            }

            $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            Log::error('Erro em pdfPlaneAuto: ' . $e->getMessage());
            flash($e->getMessage())->error();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function pdfPlaneAutoView(int $kidId, int $checklistId, int $planeId)
    {
        try {
            // Primeiro, verificar se o kid existe
            $kid = Kid::findOrFail($kidId);

            // Verificar se o checklist existe
            $checklist = Checklist::findOrFail($checklistId);

            // Verificar se o checklist pertence ao kid
            if ($checklist->kid_id != $kidId) {
                throw new Exception('Este checklist não pertence a esta criança.');
            }

            // obter o plane
            $plane = Plane::findOrFail($planeId);

            // verificar se o plane pertence ao checklist
            if ($plane->checklist_id != $checklist->id) {
                throw new Exception('Este plano não pertence a este checklist.');
            }

            // get kid
            $nameKid = $kid->name;
            $therapist = $kid->professional->name;
            $date = $plane->first()->created_at;
            $arr = [];

            // get competences do plane
            $competences = $plane->competences()->get();

            // verificar se existe competencias
            if (count($competences) == 0) {
                throw new Exception('Não existem competências para este plano.');
            }

            foreach ($competences as $c => $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->preferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'), null);

            $totalDomain = count($arr);
            $countDomain = 1;

            foreach ($arr as $initial => $v) {
                $countCompetences = 1;
                $pdf->AddPage();

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 14);

                // Domain
                $domain = $v['domain']->name;
                $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

                foreach ($v['competences'] as $k => $competence) {
                    if ($countCompetences == 8) {
                        $pdf->AddPage();
                        $countCompetences = 1;
                    }
                    $countCompetences++;

                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', 'B', 10);
                    $txt = $competence->level_id . $v['domain']->initial . $competence->code . ' - ' . $competence->description;
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
            }

            $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            Log::error('Erro em pdfPlaneAuto: ' . $e->getMessage());
            flash('Erro ao gerar o plano: ' . $e->getMessage())->error();

            return redirect()->back();
        }

        return redirect()->back();
    }

    private function preferences(MyPdf &$pdf, Kid $kid, string $therapist, int $planeId, string $date, ?string $planeName = null): void
    {
        $preferences = [
            'HideToolbar' => true,
            'HideMenubar' => true,
            'HideWindowUI' => true,
            'FitWindow' => true,
            'CenterWindow' => true,
            'DisplayDocTitle' => true,
            'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
            'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintScaling' => 'AppDefault', // None, AppDefault
            'Duplex' => 'DuplexFlipLongEdge', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
            'PickTrayByPDFSize' => true,
            'PrintPageRange' => [1, 1, 2, 3],
            'NumCopies' => 2,
        ];

        $pdf->setViewerPreferences($preferences);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 18);
        $pdf->Cell(0, 60, 'PLANO DE INTERVENÇÃO N.: ' . $planeId, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 16);
        $pdf->Write(0, $kid->name, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, $kid->FullNameMonths, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(15);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->Write(0, 'Profissional(ais)', '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, $therapist, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Data: ' . $date, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(15);

        if ($planeName) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, '(' . $planeName . ')', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(3);
        }
    }

    public function uploadPhoto(Request $request, Kid $kid): RedirectResponse
    {
        try {
            $request->validate([
                'photo' => ['required', 'image', 'max:1024'], // max 1MB
            ]);

            if ($request->hasFile('photo')) {
                $this->kidService->uploadPhoto($kid->id, $request->file('photo'));
                flash('Foto atualizada com sucesso!')->success();
            }

            return redirect()->back();
        } catch (Exception $e) {
            Log::error('Erro ao atualizar foto: ' . $e->getMessage());
            flash('Erro ao atualizar foto.')->error();

            return redirect()->back();
        }
    }

    public function showRadarChart(int $kidId, int $levelId): View
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter os domínios para o nível selecionado
        $domainLevels = DB::table('domain_level')->where('level_id', $levelId)->pluck('domain_id');
        $domains = Domain::whereIn('id', $domainLevels)->get();

        // Preparar os dados para o radar geral por domínios
        $radarDataDomains = [];
        foreach ($domains as $domain) {
            // Obter as competências do domínio e nível selecionados
            $competences = Competence::where('domain_id', $domain->id)->where('level_id', $levelId)->get();

            // Obter as avaliações da criança para essas competências
            $evaluations = DB::table('checklist_competence')
                ->join('checklists', 'checklist_competence.checklist_id', '=', 'checklists.id')
                ->where('checklists.kid_id', $kidId)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->select('competence_id', 'note')
                ->get()
                ->keyBy('competence_id');

            // Calcular a média das notas para o domínio
            $sumNotes = 0;
            $countNotes = 0;

            foreach ($competences as $competence) {
                $evaluation = $evaluations->get($competence->id);

                if ($evaluation) {
                    $note = $evaluation->note;

                    if ($note !== null && $note !== 0) {
                        $sumNotes += $note;
                        $countNotes++;
                    }
                }
            }

            if ($countNotes > 0) {
                $average = $sumNotes / $countNotes;
            } else {
                $average = null;
            }

            $radarDataDomains[] = [
                'domain' => $domain->initial,
                'average' => $average,
            ];
        }

        // Retornar a view com os dados do radar geral
        return view('kids.radar_chart', compact('kid', 'ageInMonths', 'levelId', 'radarDataDomains', 'domains'));
    }

    public function showDomainDetails(int $kidId, int $levelId, int $domainId, ?int $checklistId = null): View
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter o domínio
        $domain = Domain::findOrFail($domainId);

        // Obter o checklist atual (mais recente)
        $currentChecklist = Checklist::where('kid_id', $kidId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Obter o checklist de comparação, se um ID foi fornecido
        if ($checklistId) {
            $previousChecklist = Checklist::find($checklistId);
        } else {
            $previousChecklist = null;
        }

        if ($levelId == 0) {
            $levelId = [1, 2, 3, 4];
        } else {
            $levelId = [$levelId];
        }

        // Obter as competências do domínio e nível selecionados
        $competences = Competence::where('domain_id', $domainId)
            ->whereIn('level_id', $levelId)
            ->get();

        $ageInmonths = 0;

        // Preparar os dados para as avaliações de ambos os checklists
        $radarDataCompetences = [];
        foreach ($competences as $competence) {
            // Inicializar as notas
            $currentNote = null;
            $previousNote = null;

            // Obter a avaliação para o checklist atual
            if ($currentChecklist) {
                $currentEvaluation = DB::table('checklist_competence')
                    ->where('checklist_id', $currentChecklist->id)
                    ->where('competence_id', $competence->id)
                    ->select('note')
                    ->first();
                $currentNote = $currentEvaluation ? $currentEvaluation->note : null;
            }

            // Obter a avaliação para o checklist anterior
            if ($previousChecklist) {
                $previousEvaluation = DB::table('checklist_competence')
                    ->where('checklist_id', $previousChecklist->id)
                    ->where('competence_id', $competence->id)
                    ->select('note')
                    ->first();
                $previousNote = $previousEvaluation ? $previousEvaluation->note : null;
            }

            // Determinar os status
            $currentStatusValue = $this->getStatusValue($currentNote);
            $previousStatusValue = $this->getStatusValue($previousNote);

            // Verificar se a criança deveria passar a competência com base nos percentis
            $shouldPass = [
                '25' => $ageInMonths >= $competence->percentil_25,
                '50' => $ageInMonths >= $competence->percentil_50,
                '75' => $ageInMonths >= $competence->percentil_75,
                '90' => $ageInMonths >= $competence->percentil_90,
            ];

            $shouldPassPercentil = [
                '25' => $ageInMonths >= $competence->percentil_25,
                '50' => $ageInMonths >= $competence->percentil_50,
                '75' => $ageInMonths >= $competence->percentil_75,
                '90' => $ageInMonths >= $competence->percentil_90,
            ];

            // Determinar o progresso em termos de percentil
            // Definir status e cor inicial
            $status = 'Dentro do esperado';
            $statusColor = 'blue';

            // Analisar com base nos percentis e status
            // Analisar com base nos percentis e status
            // Aplicar a lógica para todos os percentis
            if ($currentStatusValue === 3) {
                // Consistente
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Adiantada'; // Consistente antes do esperado
                    $statusColor = 'blue';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_50) {
                    $status = 'Adiantada'; // Consistente entre 25% e 50%, ainda adiantada
                    $statusColor = 'blue';
                } elseif ($ageInmonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado'; // Consistente dentro da faixa normal (50% - 75%)
                    $statusColor = 'orange';
                } elseif ($ageInmonths < $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Consistente dentro da faixa normal (75% - 90%)
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Consistente depois do percentil 90, mas Consistente
                    $statusColor = 'orange';
                }
            } elseif ($currentStatusValue === 2) {
                // Mais ou menos
                if ($ageInmonths < $competence->percentil_25) {
                    $status = 'Dentro do esperado'; // Mais ou menos, mas ainda dentro da faixa esperada (<25%)
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_25 && $ageInmonths < $competence->percentil_50) {
                    $status = 'Dentro do esperado'; // Mais ou menos entre 25% e 50%
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_50 && $ageInmonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado'; // Mais ou menos entre 50% e 75%
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_75 && $ageInmonths < $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Mais ou menos entre 75% e 90%
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_90) {
                    $status = 'Atrasada'; // Mais ou menos após o percentil 90, deveria ter Consistente
                    $statusColor = 'red';
                }
            } elseif ($currentStatusValue === 1) {
                // Difícil de obter ou não avaliado
                if ($ageInmonths < $competence->percentil_25) {
                    $status = 'Dentro do esperado'; // Difícil de obter, mas ainda dentro da faixa esperada (<25%)
                    $statusColor = 'orange';
                } elseif ($ageInmonths >= $competence->percentil_25 && $ageInmonths < $competence->percentil_50) {
                    $status = 'Atrasada'; // Difícil de obter entre 25% e 50%
                    $statusColor = 'red';
                } elseif ($ageInmonths >= $competence->percentil_50 && $ageInmonths < $competence->percentil_75) {
                    $status = 'Atrasada'; // Difícil de obter entre 50% e 75%
                    $statusColor = 'red';
                } elseif ($ageInmonths >= $competence->percentil_75 && $ageInmonths < $competence->percentil_90) {
                    $status = 'Atrasada'; // Difícil de obter entre 75% e 90%
                    $statusColor = 'red';
                } elseif ($ageInmonths >= $competence->percentil_90) {
                    $status = 'Atrasada'; // Difícil de obter após o percentil 90, deveria ter Consistente
                    $statusColor = 'red';
                }
            }

            // Determinar o progresso em termos de percentil
            $percentComplete = 0;
            if ($ageInmonths < $competence->percentil_25) {
                $percentComplete = ($ageInmonths / $competence->percentil_25) * 25;
            } elseif ($ageInmonths < $competence->percentil_50) {
                $percentComplete = 25 + (($ageInmonths - $competence->percentil_25) / ($competence->percentil_50 - $competence->percentil_25)) * 25;
            } elseif ($ageInmonths < $competence->percentil_75) {
                $percentComplete = 50 + (($ageInmonths - $competence->percentil_50) / ($competence->percentil_75 - $competence->percentil_50)) * 25;
            } elseif ($ageInmonths < $competence->percentil_90) {
                $percentComplete = 75 + (($ageInmonths - $competence->percentil_75) / ($competence->percentil_90 - $competence->percentil_75)) * 15;
            } else {
                $percentComplete = 90 + (($ageInmonths - $competence->percentil_90) / ($competence->percentil_90)) * 10;
            }

            $radarDataCompetences[] = [
                'level' => $competence->level_id,
                'domain_initial' => $competence->domain->initial, // Nova chave adicionada
                'competence' => $competence->code,
                'description' => $competence->description,
                'currentStatusValue' => $currentStatusValue,
                'previousStatusValue' => $previousStatusValue,
                'shouldPass' => $shouldPass,
                'statusColor' => $statusColor,
                'status' => $status,
                'percentil_25' => $competence->percentil_25,
                'percentil_50' => $competence->percentil_50,
                'percentil_75' => $competence->percentil_75,
                'percentil_90' => $competence->percentil_90,
            ];
        }
        if (is_array($levelId) && count($levelId) > 1) {
            $levelId = 0;
        } else {
            $levelId = $levelId[0];
        }

        // Retornar a view com os dados do radar detalhado
        $data = [
            'kid' => $kid,
            'ageInMonths' => $ageInMonths,
            'levelId' => $levelId,
            'domain' => $domain,
            'radarDataCompetences' => $radarDataCompetences,
            'currentChecklist' => $currentChecklist,
            'previousChecklist' => $previousChecklist,
        ];

        return view('kids.domain_details', $data);
    }

    public function showRadarChart2(int $kidId, int $levelId, ?int $firstChecklistId = null, ?int $secondChecklistId = null): View
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Configurar níveis
        if ($levelId == 0) {
            $levelId = [1, 2, 3, 4];
        } else {
            $levelId = [$levelId];
        }

        // Obter os domínios para o nível selecionado
        $domainLevels = DB::table('domain_level')->whereIn('level_id', $levelId)->pluck('domain_id');
        $domains = Domain::whereIn('id', $domainLevels)->get();

        // Obter todos os checklists disponíveis
        $allChecklists = Checklist::where('kid_id', $kidId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Se nenhum checklist foi selecionado, usar os dois mais recentes
        if (!$firstChecklistId && !$secondChecklistId && $allChecklists->count() >= 2) {
            $firstChecklistId = $allChecklists->first()->id;
            $secondChecklistId = $allChecklists->skip(1)->first()->id;
        }

        // Obter os checklists selecionados
        $firstChecklist = $firstChecklistId ? Checklist::find($firstChecklistId) : null;
        $secondChecklist = $secondChecklistId ? Checklist::find($secondChecklistId) : null;

        // Preparar os dados para o radar geral por domínios
        $radarDataDomains = [];
        $levels = [];

        foreach ($domains as $domain) {
            // Obter as competências do domínio e nível selecionados
            $competences = Competence::where('domain_id', $domain->id)
                ->whereIn('level_id', $levelId)
                ->get();

            // Inicializar as médias como null
            $firstAverage = null;
            $secondAverage = null;

            // Calcular a média para o primeiro checklist
            if ($firstChecklist) {
                $firstEvaluations = DB::table('checklist_competence')
                    ->where('checklist_id', $firstChecklist->id)
                    ->whereIn('competence_id', $competences->pluck('id'))
                    ->select('competence_id', 'note')
                    ->get()
                    ->keyBy('competence_id');

                $firstSumNotes = 0;
                $firstCountNotes = 0;

                foreach ($competences as $competence) {
                    $evaluation = $firstEvaluations->get($competence->id);
                    if ($evaluation && $evaluation->note !== null && $evaluation->note !== 0) {
                        $firstSumNotes += $evaluation->note;
                        $firstCountNotes++;
                    }
                }

                $firstAverage = $firstCountNotes > 0 ? $firstSumNotes / $firstCountNotes : null;
            }

            // Calcular a média para o segundo checklist
            if ($secondChecklist) {
                $secondEvaluations = DB::table('checklist_competence')
                    ->where('checklist_id', $secondChecklist->id)
                    ->whereIn('competence_id', $competences->pluck('id'))
                    ->select('competence_id', 'note')
                    ->get()
                    ->keyBy('competence_id');

                $secondSumNotes = 0;
                $secondCountNotes = 0;

                foreach ($competences as $competence) {
                    $evaluation = $secondEvaluations->get($competence->id);
                    if ($evaluation && $evaluation->note !== null && $evaluation->note !== 0) {
                        $secondSumNotes += $evaluation->note;
                        $secondCountNotes++;
                    }
                }

                $secondAverage = $secondCountNotes > 0 ? $secondSumNotes / $secondCountNotes : null;
            }

            $radarDataDomains[] = [
                'domain' => $domain->initial,
                'firstAverage' => $firstAverage,
                'secondAverage' => $secondAverage,
            ];

            // Configurar níveis disponíveis
            if ($firstChecklist) {
                for ($i = 1; $i <= $firstChecklist->level; $i++) {
                    $levels[$i] = $i;
                }
            }
            if ($secondChecklist) {
                for ($i = 1; $i <= $secondChecklist->level; $i++) {
                    $levels[$i] = $i;
                }
            }
        }

        if (is_array($levelId) && count($levelId) > 1) {
            $levelId = 0;
        } else {
            $levelId = $levelId[0];
        }

        $data = [
            'kid' => $kid,
            'ageInMonths' => $ageInMonths,
            'levelId' => $levelId,
            'radarDataDomains' => $radarDataDomains,
            'domains' => $domains,
            'firstChecklist' => $firstChecklist,
            'secondChecklist' => $secondChecklist,
            'allChecklists' => $allChecklists,
            'levels' => $levels,
            'countChecklists' => $allChecklists->count(),
        ];

        return view('kids.radar_chart2', $data);
    }

    private function getStatusValue(?int $note): int
    {
        if ($note == 1) {
            return 1; // Difícil de obter
        } elseif ($note == 2) {
            return 2; // Mais ou menos
        } elseif ($note == 3) {
            return 3; // Consistente
        } else {
            return 0; // Não Avaliado
        }
    }

    public function overviewOld(int $kidId, ?int $levelId = null, ?int $checklistId = null): View
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

        // Verificar se existe um checklist atual
        if (!$currentChecklist) {
            throw new Exception('Nenhum checklist encontrado!');
        }

        // Obter o checklist de comparação, se um ID foi fornecido
        $previousChecklist = $checklistId ? Checklist::find($checklistId) : null;

        // Obter todos os checklists para o combobox, excluindo o atual
        $allChecklists = Checklist::where('kid_id', $kidId)
            ->where('id', '<>', $currentChecklist->id)
            ->orderBy('id', 'desc')
            ->get();

        // Obter os níveis disponíveis (assumindo que os níveis vão de 1 ao nível atual)
        $levels = [];
        for ($i = 1; $i <= $currentChecklist->level; $i++) {
            $levels[] = $i;
        }

        // Obter os domínios
        if ($levelId) {
            // Obter os domínios para o nível selecionado
            $domainLevels = DB::table('domain_level')
                ->where('level_id', $levelId)
                ->pluck('domain_id');
            $domains = Domain::whereIn('id', $domainLevels)->get();
        } else {
            // Obter todos os domínios
            $domains = Domain::all();
        }

        // Preparar os dados por domínio
        $domainData = [];
        $totalItemsTested = 0;
        $totalItemsValid = 0;
        $totalItemsInvalid = 0;
        $totalItemsTotal = 0;
        $itemsInvalid = 0;

        foreach ($domains as $domain) {
            // Obter as competências do domínio
            $competences = Competence::where('domain_id', $domain->id)
                ->when($levelId, function ($query, $levelId) {
                    return $query->where('level_id', $levelId);
                })
                ->get();

            $itemsTotal = $competences->count();

            // Contar os itens válidos (Consistentes) para a criança através do checklist
            $itemsValid = 0;

            // Obter as avaliações do checklist atual para as competências selecionadas
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

                if ($evaluation && $evaluation->note == 3) { // note 3 significa 'Consistente'
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

            $totalItemsTested += $itemsTested;
            $totalItemsValid += $itemsValid;
            $totalItemsInvalid += $itemsInvalid;
            $totalItemsTotal += $itemsTotal;
        }

        // percentual total te dos os dominios
        $totalPercentageGeral = 0;
        $totalDomains = count($domainData);
        foreach ($domainData as $domain) {
            $percentage = $domain['itemsTested'] > 0 ? ($domain['itemsValid'] / $domain['itemsTested']) * 100 : 0;
            $totalPercentageGeral += $percentage;
        }
        $averagePercentage = round($totalDomains > 0 ? $totalPercentageGeral / $totalDomains : 0, 2);

        // Calcular o percentual total
        $totalPercentage = $totalItemsTested > 0 ? ($totalItemsValid / $totalItemsTested) * 100 : 0;

        // Calcular a idade de desenvolvimento
        $developmentalAgeInMonths = $ageInMonths * ($totalPercentage / 100);
        $delayInMonths = $ageInMonths - $developmentalAgeInMonths;

        // Identificar áreas frágeis (exemplo: percentuais abaixo de 50%)
        $weakAreas = array_filter($domainData, function ($domain) {
            return $domain['percentage'] < 100;
        });

        // Ordenar as áreas frágeis do menor para o maior percentual
        usort($weakAreas, function ($a, $b) {
            return $a['percentage'] <=> $b['percentage'];
        });

        // Passar os dados para a view
        return view('kids.overview', compact(
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
        ));
    }

    public function overview(Request $request, int $kidId, ?int $levelId = null): View
    {
        // Capturar checklistId da query string
        $checklistId = $request->query('checklist_id');

        // Usando o serviço para obter os dados
        $data = $this->overviewService->getOverviewData($kidId, $levelId, $checklistId);

        // Retornar a view com os dados processados
        return view('kids.overview', $data);
    }

    public function generatePdf(Request $request, int $kidId, ?int $levelId = null): RedirectResponse|\Illuminate\Http\Response
    {
        // Capturar checklistId da query string
        $checklistId = $request->query('checklist_id');

        // Reutilizar o serviço para obter os dados da visão geral com o checklist correto
        $data = $this->overviewService->getOverviewData($kidId, $levelId, $checklistId);

        // Obter as imagens dos gráficos enviadas no request
        $barChartImage = $request->input('barChartImage');
        $radarChartImage = $request->input('radarChartImage');
        $barChartItems2Image = $request->input('barChartItems2Image');

        $kid = Kid::findOrFail($kidId);

        // Usar o checklist dos dados retornados pelo service (que pode ser o selecionado ou o atual)
        $currentChecklist = $data['currentChecklist'];

        if (!$currentChecklist) {
            flash('Não é possível gerar PDF. Esta criança não possui checklist avaliado.')->warning();

            return redirect()->back();
        }

        $createdAt = Carbon::parse($currentChecklist->created_at)->format('d/m/Y');

        // Obter a data atual formatada
        $currentDate = Carbon::now()->format('d/m/Y');

        // Montar a saída com informação do checklist
        $checklistInfo = $checklistId ? 'Checklist #' . $currentChecklist->id : 'Checklist Atual';
        $periodAvaliable = "Período de avaliação: {$createdAt} até {$currentDate} ({$checklistInfo})";

        // Criar uma nova instância do PDF
        $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Adicionar uma página
        $pdf->AddPage();

        // Definir o caminho para o logo
        $logoPath = public_path('images/logo_login.png');
        $pdf->Ln(10);
        // Verificar se o arquivo existe
        if (file_exists($logoPath)) {
            // Definir a largura do logo
            $logoWidth = 60; // Ajuste conforme necessário
            // Obter a largura da página
            $pageWidth = $pdf->getPageWidth();
            // Calcular a posição X para centralizar
            $x = ($pageWidth - $logoWidth) / 2;
            // Adicionar o logo
            $pdf->Image($logoPath, $x, '', $logoWidth, '', 'PNG');
        }

        // Adicionar espaço após o logo
        $pdf->Ln(40);

        // Definir o título do PDF
        $pdf->SetFont('helvetica', '', 22);
        $pdf->Cell(0, 10, 'Prontuário de desenvolvimento', 0, 1, 'C');
        $pdf->Ln(10);

        // Obter o caminho da foto da criança
        $photoPath = storage_path('app/public/' . $kid->photo);

        // Verificar se o arquivo existe
        if (file_exists($photoPath)) {
            // Definir a largura da foto
            $photoWidth = 50; // Ajuste conforme necessário
            // Obter a largura da página
            $pageWidth = $pdf->getPageWidth();
            // Calcular a posição X para centralizar
            $x = round(($pageWidth - $photoWidth) / 2, 0);
            // Adicionar a foto da criança
            // dd($x);
            $pdf->Image($photoPath, 80, null, $photoWidth, $photoWidth, '', '', 'C', false, 72);
            // Adicionar espaço após a foto
            $pdf->Ln(60);
        }

        // Adicionar as informações principais (por exemplo, nome da criança e idade)
        $pdf->SetFont('helvetica', '', 12);

        $preferences = [
            'HideToolbar' => true,
            'HideMenubar' => true,
            'HideWindowUI' => true,
            'FitWindow' => true,
            'CenterWindow' => true,
            'DisplayDocTitle' => true,
            'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
            'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintScaling' => 'AppDefault', // None, AppDefault
            'Duplex' => 'DuplexFlipLongEdge', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
            'PickTrayByPDFSize' => true,
            'PrintPageRange' => [1, 1, 2, 3],
            'NumCopies' => 2,
        ];
        $pdf->setViewerPreferences($preferences);

        $pdf->SetFont('helvetica', '', 18);
        $pdf->Write(0, $kid->name, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->Write(0, $kid->FullNameMonths, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', '', 14);

        $professionals = $kid->professionals()->get();
        $professionalNames = [];
        foreach ($professionals as $professional) {
            $professionalNames[] = $professional->user->first()->name . ' (' . $professional->specialty->name . ')';
        }
        $txt = 'Profissionais: ' . implode(', ', $professionalNames);

        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(1);

        $txt = 'Responsável: ' . $kid->responsible->name;
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(1);

        $txt = 'Desenvolvimento: ' . round($data['developmentalAgeInMonths'], 0) . ' meses';
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(1);

        $txt = 'Atraso: ' . round($data['delayInMonths'], 0) . ' meses';
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(1);

        $txt = $periodAvaliable;
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(1);

        // $pdf->Ln(20);
        // $txt2 = "Esta avaliação foi composta pelo instrumento Checklist Curriculum Denver. Mantivemos como base de aferição o Nível III do Checklist Curriculum Denver para efeitos de comparação em relação ao próprio desenvolvimento de " . $kid->name . ". Os resultados estão ilustrados abaixo:";
        // $pdf->SetFont('helvetica', '', 12);
        // $pdf->MultiCell(0, 5, $txt2, 0, 'L', 0, 1);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 14, '', 'C');

        $pdf->Cell(0, 10, 'Relatório de Visão Geral da Criança', 0, 1, 'C');
        $this->addChartToPdf($pdf, $barChartImage, 'Gráfico de Barras: Percentual de Habilidades', 170);

        $pdf->AddPage();
        $this->addChartToPdf($pdf, $radarChartImage, 'Gráfico de Radar: Análise de Competências', 306);

        $pdf->AddPage();
        $this->addChartToPdf($pdf, $barChartItems2Image, 'Análise Geral dos Itens', 170);

        // Adicionar tabela de domínios
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, 'Domínios Avaliados', 0, 1, 'C');

        $pdf->SetFont('courier', '', 10);
        $html = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th style="bold; background-color: #f2f2f2;" nowrap="nowrap">Domínio</th>
                    <th style="bold; background-color: #f2f2f2;">Total Itens</th>
                    <th style="bold; background-color: #f2f2f2;">Itens Testados</th>
                    <th style="bold; background-color: #f2f2f2;">Itens Válidos</th>
                    <th style="bold; background-color: #f2f2f2;">Itens Inválidos</th>
                    <th style="bold; background-color: #f2f2f2;">Percentual (%)</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['domainData'] as $domain) {
            $color = $this->getProgressColor($domain['percentage']);
            $html .= '<tr>
                <td nowrap="nowrap">' . $domain['name'] . '</td>
                <td style="text-align: center;">' . $domain['itemsTotal'] . '</td>
                <td style="text-align: center;">' . $domain['itemsTested'] . '</td>
                <td style="text-align: center;">' . $domain['itemsValid'] . '</td>
                <td style="text-align: center;">' . $domain['itemsInvalid'] . '</td>
                <td style="text-align: left;">
                    <div style="position: relative; width: 100%;">
                        <div style="width: ' . $domain['percentage'] . '%; background-color: ' . $color . '; color: white; text-align: left; padding: 2px; border-radius: 3px;">
                            ' . $domain['percentage'] . '%
                        </div>
                    </div>
                </td>
            </tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Adicionar tabela de Áreas Frágeis
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, 'Áreas Frágeis', 0, 1, 'C');

        $pdf->SetFont('courier', '', 10);

        $html = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="white-space: nowrap; font-weight: bold; background-color: #f2f2f2;">Domínio</th>
                            <th style="white-space: nowrap; font-weight: bold; background-color: #f2f2f2;">Percentual (%)</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($data['weakAreas'] as $domain) {
            $color = $this->getProgressColor($domain['percentage']);
            $html .= '<tr>
                <td style="white-space: nowrap;">' . $domain['name'] . '</td>
                <td style="text-align: left;">
                    <div style="position: relative; width: 100%;">
                        <div style="width: ' . $domain['percentage'] . '%; background-color: ' . $color . '; color: white; text-align: left; padding: 2px; border-radius: 3px;">
                            ' . $domain['percentage'] . '%
                        </div>
                    </div>
                </td>
            </tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Gerar o PDF e retorná-lo como resposta
        return response($pdf->Output('overview.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    // Método auxiliar para adicionar gráficos ao PDF
    private function addChartToPdf(MyPdf $pdf, ?string $imageData, string $title, ?int $width = null, ?int $height = null): void
    {
        // Adicionar título sempre, mesmo se não houver imagem
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');

        if ($imageData && $imageData !== 'data:,') {
            try {
                // Verificar se a imagem tem conteúdo válido
                if (strpos($imageData, 'data:image') === 0) {
                    // Decodificar a imagem base64
                    $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    if ($decodedImage && strlen($decodedImage) > 100) { // Verificar se tem conteúdo mínimo
                        // Criar um caminho absoluto para o arquivo temporário
                        $tempDir = storage_path('app/temp');

                        // Garantir que o diretório existe com permissões corretas
                        if (!file_exists($tempDir)) {
                            mkdir($tempDir, 0755, true);
                        }

                        // Criar nome único para o arquivo
                        $tempFileName = uniqid('chart_') . '.png';
                        $tempImagePath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;

                        // Salvar a imagem
                        if (file_put_contents($tempImagePath, $decodedImage)) {
                            // Verificar se o arquivo existe e é legível
                            if (file_exists($tempImagePath) && is_readable($tempImagePath) && filesize($tempImagePath) > 100) {
                                // Calcular a posição X para centralizar a imagem
                                $pageWidth = $pdf->getPageWidth();
                                $x = ($pageWidth - $width) / 2;

                                // Adicionar imagem centralizada
                                $pdf->Image($tempImagePath, $x, '', $width, $height, 'PNG');
                                \Log::info("Gráfico '$title' adicionado com sucesso ao PDF");
                            } else {
                                \Log::warning("Arquivo de imagem não é válido para '$title'");
                                $this->addErrorMessage($pdf, 'Gráfico não disponível');
                            }

                            // Remover o arquivo temporário
                            if (file_exists($tempImagePath)) {
                                unlink($tempImagePath);
                            }
                        } else {
                            \Log::error("Falha ao salvar arquivo temporário para '$title'");
                            $this->addErrorMessage($pdf, 'Erro ao processar gráfico');
                        }
                    } else {
                        \Log::warning("Dados de imagem insuficientes para '$title'");
                        $this->addErrorMessage($pdf, 'Dados de gráfico insuficientes');
                    }
                } else {
                    \Log::warning("Formato de imagem inválido para '$title'");
                    $this->addErrorMessage($pdf, 'Formato de gráfico inválido');
                }
            } catch (Exception $e) {
                \Log::error("Erro ao processar imagem para PDF '$title': " . $e->getMessage());
                $this->addErrorMessage($pdf, 'Erro ao carregar gráfico: ' . $e->getMessage());
            }
        } else {
            \Log::warning("Nenhuma imagem fornecida para '$title'");
            $this->addErrorMessage($pdf, 'Gráfico não foi gerado');
        }
    }

    private function addErrorMessage(MyPdf $pdf, string $message): void
    {
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->SetTextColor(128, 128, 128); // Cor cinza
        $pdf->Cell(0, 10, $message, 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0); // Restaurar cor preta
        $pdf->Ln(20);
    }

    private function getProgressColor(float $percentage): string
    {
        $roundedPercentage = (int) round($percentage / 10) * 10;
        $roundedPercentage = max(0, min(100, $roundedPercentage));

        return match ($roundedPercentage) {
            0 => '#6a2046',    // Mais escuro para 0%
            10 => '#8a2e5c',   // Escuro
            20 => '#a34677',
            30 => '#a7527f',
            40 => '#ab5e88',
            50 => '#af6a90',
            60 => '#b37698',
            70 => '#bb8ea9',
            80 => '#bf9ab1',
            90 => '#c3a6ba',
            100 => '#f7e6f2',  // Mais claro para 100%
            default => '#6a2046',
        };
    }
}
