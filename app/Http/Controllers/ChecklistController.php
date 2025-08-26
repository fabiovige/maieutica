<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Models\ChecklistCompetence;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Kid;
use App\Models\Plane;
use App\Services\ChecklistService;
use App\Contracts\ChecklistRepositoryInterface;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChecklistController extends BaseController
{
    protected $checklistService;
    protected $checklistRepository;

    public function __construct(
        ChecklistService $checklistService,
        ChecklistRepositoryInterface $checklistRepository
    ) {
        $this->checklistService = $checklistService;
        $this->checklistRepository = $checklistRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Checklist::class);

        $kid = $request->kidId ? Kid::findOrFail($request->kidId) : null;

        return $this->handleIndexRequest(
            $request,
            function ($filters) use ($request, $kid) {
                if ($kid) {
                    $checklists = $this->checklistRepository->getChecklistsByKid($kid->id, $filters);
                } else {
                    $checklists = $this->checklistRepository->getChecklistsForUser(auth()->id(), $filters);
                }

                foreach ($checklists as $checklist) {
                    $checklist->developmentPercentage = $this->checklistService->percentualDesenvolvimento($checklist->id);
                    
                    // Formatar dados para exibição
                    $checklist->status_badge = '<span class="badge bg-' . 
                        ($checklist->situation == 'a' ? 'success' : 'secondary') . '">' . 
                        ($checklist->situation == 'a' ? 'Aberto' : 'Fechado') . '</span>';
                    
                    $checklist->formatted_date = $checklist->created_at->format('d/m/Y');
                    
                    $percentage = $checklist->developmentPercentage ?? 0;
                    $color = $percentage < 30 ? 'danger' : ($percentage < 70 ? 'warning' : 'success');
                    $checklist->progress_bar = '<div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-' . $color . '" role="progressbar" 
                             style="width: ' . $percentage . '%;" 
                             aria-valuenow="' . $percentage . '" 
                             aria-valuemin="0" aria-valuemax="100">' . 
                             number_format($percentage, 1) . '%
                        </div>
                    </div>';
                }

                return $checklists;
            },
            'checklists.index',
            ['kid' => $kid],
            'checklists'
        );
    }

    public function create()
    {
        $this->authorize('create', Checklist::class);

        return $this->handleCreateRequest(
            fn() => [
                'kids' => Kid::getKids()
            ],
            'checklists.create',
            [],
            'Erro ao carregar formulário de criação',
            'checklists.index'
        );
    }

    public function store(ChecklistRequest $request)
    {
        $this->authorize('create', Checklist::class);

        try {
            $message = label_case('Store Checklists ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $data = $request->json()->all() ?? $request->all();
            $data['created_by'] = Auth::id();

            // Validação da data retroativa
            if (isset($data['created_at']) && $data['created_at']) {
                $createdAt = \Carbon\Carbon::parse($data['created_at']);
                // Não permitir datas futuras
                if ($createdAt->isFuture()) {
                    return response()->json(['error' => 'A data não pode ser futura.'], 422);
                }
                $data['created_at'] = $createdAt;
                // Se a data não for hoje, checklist deve ser fechado
                if (!$createdAt->isToday()) {
                    $data['situation'] = 'f';
                } else {
                    $data['situation'] = 'a';
                }
            } else {
                unset($data['created_at']); // Garante que o Eloquent use a data atual
                $data['situation'] = 'a';
            }

            // checklist
            $checklist = Checklist::create($data);

            // Plane
            $plane = Plane::create([
                'kid_id' => $request->kid_id,
                'checklist_id' => $checklist->id,
                'created_by' => Auth::id(),
            ]);

            // levels
            $arrLevel = [];
            for ($i = 1; $i <= $data['level']; $i++) {
                $arrLevel[] = $i;
            }

            // Se for retroativo, tenta clonar as notas do checklist ativo
            $clonarNotas = false;
            if (isset($data['created_at']) && !$data['created_at'] instanceof \Carbon\Carbon) {
                $data['created_at'] = \Carbon\Carbon::parse($data['created_at']);
            }
            if (isset($data['created_at']) && !$data['created_at']->isToday()) {
                $clonarNotas = true;
            }
            if ($clonarNotas) {
                $checklistAtual = Checklist::where('kid_id', $request->kid_id)
                    ->where('situation', 'a')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($checklistAtual) {
                    foreach ($arrLevel as $level) {
                        $components = \App\Models\Competence::where('level_id', '=', $level)->pluck('id')->toArray();
                        $notes = [];
                        foreach ($components as $competence_id) {
                            $chechlistCompetente = \App\Models\ChecklistCompetence::where('checklist_id', $checklistAtual->id)->where('competence_id', $competence_id)->first();
                            $notes[$competence_id] = ['note' => $chechlistCompetente ? $chechlistCompetente->note : 0];
                        }
                        $checklist->competences()->syncWithoutDetaching($notes);
                    }
                } else {
                    // Não existe checklist ativo, mantém notas zeradas
                    foreach ($arrLevel as $level) {
                        $components = \App\Models\Competence::where('level_id', '=', $level)->pluck('id')->toArray();
                        $notes = [];
                        foreach ($components as $v) {
                            $notes[$v] = ['note' => 0];
                        }
                        $checklist->competences()->syncWithoutDetaching($notes);
                    }
                }
            } else {
                // Checklist de hoje, mantém notas zeradas
                foreach ($arrLevel as $level) {
                    $components = \App\Models\Competence::where('level_id', '=', $level)->pluck('id')->toArray();
                    $notes = [];
                    foreach ($components as $v) {
                        $notes[$v] = ['note' => 0];
                    }
                    $checklist->competences()->syncWithoutDetaching($notes);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            Log::error('Erro ao criar checklist: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $message = label_case('Create Checklists ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('view', $checklist);

        return $this->handleViewRequest(
            fn() => [
                'checklist' => $checklist
            ],
            'checklists.show',
            [],
            'Erro ao carregar dados do checklist',
            'checklists.index'
        );
    }

    public function edit($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('update', $checklist);

        return $this->handleViewRequest(
            fn() => [
                'checklist' => $checklist
            ],
            'checklists.edit',
            [],
            'Erro ao carregar dados do checklist',
            'checklists.index'
        );
    }

    public function update(ChecklistRequest $request, $id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('update', $checklist);

        try {
            $data = $request->all();
            $data['updated_by'] = Auth::id();
            
            if (isset($data['situation'])) {
                $checklist->situation = $data['situation'];
            }
            
            $checklist->update($data);
            
            flash(self::MSG_UPDATE_SUCCESS)->success();
            
            // Redirecionamento condicional
            if ($request->has('kidId') || $request->query('kidId')) {
                $kidId = $request->input('kidId', $request->query('kidId', $checklist->kid_id));
                return redirect()->to('checklists?kidId=' . $kidId);
            }
            
            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            Log::error(self::MSG_UPDATE_ERROR . ': ' . $e->getMessage());
            flash(self::MSG_UPDATE_ERROR)->error();
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('delete', $checklist);

        return $this->handleUpdateRequest(
            function() use ($checklist) {
                if ($checklist->planes()->exists()) {
                    foreach ($checklist->planes as $plane) {
                        $plane->deleted_by = Auth::id();
                        $plane->save();
                        $plane->delete();
                    }
                }
                
                $this->checklistRepository->markAsDeleted($checklist, Auth::id());
            },
            self::MSG_DELETE_SUCCESS,
            self::MSG_DELETE_ERROR,
            'checklists.index'
        );
    }

    public function fill($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('view', $checklist);

        try {
            $message = label_case('Fill Checklist ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);
            $data = [
                'is_admin' => auth()->user()->isAdmin(),
                'situation' => $checklist->situation,
                'checklist_id' => $id,
                'level_id' => $checklist->level,
                'created_at' => $checklist->created_at->format('d/m/Y') . ' Cod. ' . $id,
                'kid' => $checklist->kid,
            ];

            return view('checklists.fill', $data);
        } catch (\Exception $e) {
            $message = label_case('Fill Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            dd($e->getMessage());
            flash(self::MSG_NOT_FOUND)->warning();
            // return redirect()->back();
        }
    }

    public function chart($id)
    {
        try {
            $message = label_case('Esfera Checklist ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);
            $checklist = Checklist::findOrFail($id);

            $data = [
                'checklists' => $checklist->kid->checklists()->get(),
                'checklist' => $checklist,
                'level_id' => $checklist->level,
                'kid' => $checklist->kid,
            ];

            return view('checklists.chart', $data);
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Fill Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function percentualDesenvolvimentoOld($checklistId)
    {
        // Obter o checklist pelo ID
        $currentChecklist = Checklist::findOrFail($checklistId);

        // Verificar se o checklist foi encontrado
        if (!$currentChecklist) {
            throw new Exception('Checklist não encontrado!');
        }

        // Obter todos os domínios
        $domains = Domain::all();

        // Variáveis para calcular o percentual de progresso
        $totalItemsTested = 0;
        $totalItemsValid = 0;

        foreach ($domains as $domain) {
            // Obter todas as competências do domínio
            $competences = Competence::where('domain_id', $domain->id)->get();

            // Obter as avaliações do checklist atual para as competências selecionadas
            $currentEvaluations = DB::table('checklist_competence')
                ->where('checklist_id', $currentChecklist->id)
                ->where('note', '<>', 0) // Considera apenas as avaliações com nota
                ->whereIn('competence_id', $competences->pluck('id'))
                ->select('competence_id', 'note')
                ->get()
                ->keyBy('competence_id');

            $itemsTested = $currentEvaluations->count();

            $itemsValid = 0;
            foreach ($competences as $competence) {
                $evaluation = $currentEvaluations->get($competence->id);

                if ($evaluation && $evaluation->note == 3) { // note 3 significa 'Consistente'
                    $itemsValid++;
                }
            }

            // Somar os itens testados e válidos
            $totalItemsTested += $itemsTested;
            $totalItemsValid += $itemsValid;

            $domainData[] = [
                'itemsTested' => $itemsTested,
                'itemsValid' => $itemsValid,
            ];
        }

        // percentual total te dos os dominios
        $totalPercentageGeral = 0;
        $totalDomains = count($domainData);
        foreach ($domainData as $domain) {
            $percentage = $domain['itemsTested'] > 0 ? ($domain['itemsValid'] / $domain['itemsTested']) * 100 : 0;
            $totalPercentageGeral += $percentage;
        }
        $averagePercentage = round($totalDomains > 0 ? $totalPercentageGeral / $totalDomains : 0, 2);

        return $averagePercentage;
    }

    public function clonarChecklist(Request $request, $id = null)
    {
        $this->authorize('create', Checklist::class);

        return $this->handleStoreRequest(
            function() use ($request, $id) {
                DB::beginTransaction();

                $checklistAtual = Checklist::where('id', $id)->firstOrFail();

                $data = [
                    'kid_id' => $checklistAtual->kid_id,
                    'situation' => 'a',
                    'level' => $checklistAtual->level,
                    'created_by' => Auth::id(),
                ];

                $checklist = Checklist::create($data);

                Plane::create([
                    'kid_id' => $checklistAtual->kid_id,
                    'checklist_id' => $checklist->id,
                    'created_by' => Auth::id(),
                ]);

                $arrLevel = range(1, $data['level']);

                foreach ($arrLevel as $level) {
                    $components = Competence::where('level_id', $level)->pluck('id')->toArray();
                    $notes = [];
                    foreach ($components as $competence_id) {
                        $chechlistCompetente = ChecklistCompetence::where('checklist_id', $checklistAtual->id)
                            ->where('competence_id', $competence_id)
                            ->first();
                        $notes[$competence_id] = ['note' => $chechlistCompetente ? $chechlistCompetente->note : 0];
                    }
                    $checklist->competences()->syncWithoutDetaching($notes);
                }

                DB::commit();
            },
            self::MSG_CLONE_SUCCESS,
            self::MSG_CLONE_ERROR,
            'checklists.index'
        );
    }
}
