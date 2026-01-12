<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Models\ChecklistCompetence;
use App\Models\ChecklistRegister;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Kid;
use App\Models\Plane;
use App\Services\ChecklistService;
use App\Services\Logging\ChecklistLogger;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChecklistController extends Controller
{

    protected $checklistService;
    protected $checklistLogger;

    public function __construct(ChecklistService $checklistService, ChecklistLogger $checklistLogger)
    {
        $this->checklistService = $checklistService;
        $this->checklistLogger = $checklistLogger;
    }

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Checklist::class);

            $queryChecklists = Checklist::getChecklists();

            $kid = $request->kidId ? Kid::findOrFail($request->kidId) : null;

            // Filtro de busca geral (nome da criança, ID do checklist)
            if ($request->filled('search')) {
                $search = $request->search;
                $queryChecklists->where(function($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhereHas('kid', function($kidQuery) use ($search) {
                          $kidQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            // Se kidId foi passado, filtra por essa criança
            if ($request->kidId) {
                $queryChecklists->where('kid_id', $request->kidId);
            }
            // Se não foi passado kidId, aplica filtros baseados no tipo de usuário
            elseif (!auth()->user()->can('checklist-list-all')) {
                // Se for profissional, filtra pelos kids vinculados
                if (auth()->user()->professional->count() > 0) {
                    $professionalId = auth()->user()->professional->first()->id;
                    $queryChecklists->whereHas('kid.professionals', function ($query) use ($professionalId) {
                        $query->where('professional_id', $professionalId);
                    });
                }
                // Se for responsável (pai), filtra pelos kids sob sua responsabilidade
                else {
                    $kids = Kid::where('responsible_id', auth()->user()->id)->pluck('id');
                    $queryChecklists->whereIn('kid_id', $kids);
                }
            }

            $checklists = $queryChecklists->with('competences')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(self::PAGINATION_DEFAULT);

            foreach ($checklists as $checklist) {
                $checklist->developmentPercentage = $this->percentualDesenvolvimento($checklist->id);
            }

            $this->checklistLogger->listed([
                'search' => $request->input('search'),
                'kid_id' => $request->kidId,
                'total_results' => $checklists->count(),
            ]);

            $data = [
                'checklists' => $checklists,
                'kid' => $kid
            ];

            return view('checklists.index', $data);
        } catch (Exception $e) {
            $this->checklistLogger->operationFailed('index', $e);

            flash($e->getMessage())->warning();

            return redirect()->back();
        }
    }

    public function create()
    {
        $this->authorize('create', Checklist::class);

        $kids = Kid::getKids();

        return view('checklists.create', compact('kids'));
    }

    public function store(ChecklistRequest $request)
    {
        $this->authorize('create', Checklist::class);

        try {
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

            // Log plane auto-generation
            $this->checklistLogger->planeAutoGenerated($checklist, $plane->id, [
                'source' => 'controller',
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

            // Observer will log at model level
            $this->checklistLogger->created($checklist, [
                'source' => 'controller',
                'retroactive' => isset($data['created_at']) && !$data['created_at']->isToday(),
                'cloned_from_active' => $clonarNotas,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            $this->checklistLogger->operationFailed('store', $e, [
                'kid_id' => $request->kid_id ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('view', $checklist);

        try {
            $this->checklistLogger->viewed($checklist, 'details');

            return view('checklists.show', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            $this->checklistLogger->operationFailed('show', $e, [
                'checklist_id' => $id,
            ]);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $this->authorize('update', Checklist::findOrFail($id));

        try {
            $checklist = Checklist::findOrFail($id);

            $this->checklistLogger->viewed($checklist, 'edit');

            return view('checklists.edit', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            $this->checklistLogger->operationFailed('edit', $e, [
                'checklist_id' => $id,
            ]);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function update(ChecklistRequest $request, $id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('update', $checklist);

        DB::beginTransaction();
        try {
            // Get original data for change tracking
            $originalData = $checklist->only(['situation', 'level', 'kid_id']);

            // Atualizar dados da criança se fornecidos
            $kidChanges = [];
            if ($request->has('kid_name') || $request->has('kid_birth_date')) {
                $kid = $checklist->kid;
                $kidOriginalData = $kid->only(['name', 'birth_date']);

                if ($request->filled('kid_name')) {
                    $kid->name = $request->kid_name;
                }

                if ($request->filled('kid_birth_date')) {
                    $kid->birth_date = $request->kid_birth_date;
                }

                $kid->updated_by = Auth::id();
                $kid->save();

                // Track kid changes
                $kidNewData = $kid->only(['name', 'birth_date']);
                foreach ($kidNewData as $key => $value) {
                    if ($kidOriginalData[$key] != $value) {
                        $kidChanges[$key] = ['old' => $kidOriginalData[$key], 'new' => $value];
                    }
                }

                if (!empty($kidChanges)) {
                    $this->checklistLogger->kidDataUpdatedViaChecklist($checklist, $kidChanges, [
                        'source' => 'controller',
                    ]);
                }
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();
            // Permitir atualização manual da situação (aberto/fechado)
            if (isset($data['situation'])) {
                $checklist->situation = $data['situation'];
            }
            $checklist->update($data);

            // Track what changed in checklist
            $changes = [];
            $newData = $checklist->only(['situation', 'level', 'kid_id']);
            foreach ($newData as $key => $value) {
                if ($originalData[$key] != $value) {
                    $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
                }
            }

            // Observer will log at model level
            if (!empty($changes)) {
                $this->checklistLogger->updated($checklist, $changes, [
                    'source' => 'controller',
                    'kid_data_updated' => !empty($kidChanges),
                ]);
            }

            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();

            // Redirecionamento condicional
            if ($request->has('kidId') || $request->query('kidId')) {
                $kidId = $request->input('kidId', $request->query('kidId', $checklist->kid_id));
                return redirect()->to('checklists?kidId=' . $kidId);
            }
            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->checklistLogger->operationFailed('update', $e, [
                'checklist_id' => $id,
            ]);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('delete', $checklist);

        DB::beginTransaction();
        try {
            $planesCount = 0;

            // Marca os planes associados como deletados
            if ($checklist->planes()->exists()) {
                foreach ($checklist->planes as $plane) {
                    $plane->deleted_by = Auth::id();
                    $plane->save();
                    $plane->delete();
                    $planesCount++;
                }
            }

            // Marca quem excluiu e move para lixeira
            $checklist->deleted_by = Auth::id();
            $checklist->save();
            $checklist->delete();

            // Observer will log at model level
            $this->checklistLogger->deleted($checklist, [
                'source' => 'controller',
                'planes_also_deleted' => $planesCount,
            ]);

            DB::commit();

            flash('Checklist movido para a lixeira com sucesso.')->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->checklistLogger->operationFailed('destroy', $e, [
                'checklist_id' => $id,
            ]);

            flash($e->getMessage())->warning();

            return redirect()->back();
        }
    }

    public function trash()
    {

        //$this->authorize('viewTrash', Checklist::class);

        $query = Checklist::onlyTrashed()->with(['kid', 'competences']);

        // Aplica filtros baseados no tipo de usuário
        if (!auth()->user()->can('checklist-list-all')) {
            // Se for profissional, filtra pelos kids vinculados
            if (auth()->user()->professional->count() > 0) {
                $professionalId = auth()->user()->professional->first()->id;
                $query->whereHas('kid.professionals', function ($q) use ($professionalId) {
                    $q->where('professional_id', $professionalId);
                });
            }
            // Se for responsável (pai), filtra pelos kids sob sua responsabilidade
            else {
                $kids = Kid::where('responsible_id', auth()->user()->id)->pluck('id');
                $query->whereIn('kid_id', $kids);
            }
        }

        $checklists = $query->orderBy('deleted_at', 'desc')->get();

        foreach ($checklists as $checklist) {
            try {
                // Usa o ChecklistService para calcular o percentual
                $checklist->developmentPercentage = $this->checklistService->percentualDesenvolvimento($checklist->id, true);
            } catch (\Exception $e) {
                // Se falhar, tenta calcular manualmente ou define como 0
                $totalCompetences = $checklist->competences->count();
                $testedCompetences = $checklist->competences->where('pivot.note', '>', 0)->count();
                $checklist->developmentPercentage = $totalCompetences > 0
                    ? round(($testedCompetences / $totalCompetences) * 100, 2)
                    : 0;
            }
        }

        $this->checklistLogger->trashViewed([
            'total_trashed' => $checklists->count(),
        ]);

        return view('checklists.trash', compact('checklists'));
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $checklist = Checklist::onlyTrashed()->findOrFail($id);

            $this->authorize('restore', $checklist);

            // Restaura o checklist da lixeira
            $checklist->restore();

            $planesCount = 0;

            // Restaura os planes associados
            if ($checklist->planes()->onlyTrashed()->exists()) {
                foreach ($checklist->planes()->onlyTrashed()->get() as $plane) {
                    $plane->restore();
                    $planesCount++;
                }
            }

            // Observer will log at model level
            $this->checklistLogger->restored($checklist, [
                'source' => 'controller',
                'planes_also_restored' => $planesCount,
            ]);

            DB::commit();

            flash('Checklist restaurado com sucesso.')->success();

            return redirect()->route('checklists.trash');
        } catch (Exception $e) {
            DB::rollBack();

            $this->checklistLogger->operationFailed('restore', $e, [
                'checklist_id' => $id,
            ]);

            flash('Erro ao restaurar checklist: ' . $e->getMessage())->warning();

            return redirect()->back();
        }
    }

    public function fill($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('view', $checklist);
        try {
            $this->checklistLogger->fillInterfaceAccessed($checklist);

            $data = [
                'is_admin' => auth()->user()->can('checklist-edit-all'),
                'situation' => $checklist->situation,
                'checklist_id' => $id,
                'level_id' => $checklist->level,
                'created_at' => $checklist->created_at->format('d/m/Y') . ' Cod. ' . $id,
                'kid' => $checklist->kid,
            ];

            return view('checklists.fill', $data);
        } catch (\Exception $e) {
            $this->checklistLogger->operationFailed('fill', $e, [
                'checklist_id' => $id,
            ]);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function chart($id)
    {
        try {
            $checklist = Checklist::findOrFail($id);

            $this->checklistLogger->chartViewed($checklist, 'radar');

            $data = [
                'checklists' => $checklist->kid->checklists()->get(),
                'checklist' => $checklist,
                'level_id' => $checklist->level,
                'kid' => $checklist->kid,
            ];

            return view('checklists.chart', $data);
        } catch (Exception $e) {
            $this->checklistLogger->operationFailed('chart', $e, [
                'checklist_id' => $id,
            ]);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function percentualDesenvolvimento($checklistId){
        return $this->checklistService->percentualDesenvolvimento($checklistId);
    }

    public function percentualDesenvolvimentoOld($checklistId)
    {
        // Obter o checklist pelo ID
        $currentChecklist = Checklist::findOrFail($checklistId);

        // Verificar se o checklist foi encontrado
        if (! $currentChecklist) {
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

        if (! auth()->user()->can('checklist-create')) {
            flash('Você não tem permissão para clonar checklists.')->warning();

            return redirect()->route('checklists.index');
        }

        try {
            DB::beginTransaction();

            $checklistAtual = Checklist::where('id', $id)->firstOrFail();


            $data = [];
            $data['kid_id'] = $checklistAtual->kid_id;
            $data['situation'] = 'a';
            $data['level'] = $checklistAtual->level;
            $data['created_by'] = Auth::id();

            // checklist
            $checklist = Checklist::create($data);

            // Plane
            $plane = Plane::create([
                'kid_id' => $checklistAtual->kid_id,
                'checklist_id' => $checklist->id,
                'created_by' => Auth::id(),
            ]);

            // levels
            $arrLevel = [];
            for ($i = 1; $i <= $data['level']; $i++) {
                $arrLevel[] = $i;
            }

            foreach ($arrLevel as $c => $level) {
                $components = Competence::where('level_id', '=', $level)->pluck('id')->toArray();
                $notes = [];
                foreach ($components as $c => $competence_id) {
                    $chechlistCompetente = ChecklistCompetence::where('checklist_id', $checklistAtual->id)->where('competence_id', $competence_id)->first();
                    $notes[$competence_id] = ['note' => $chechlistCompetente ? $chechlistCompetente->note : 0];
                }
                $checklist->competences()->syncWithoutDetaching($notes);
            }

            // Observer will log at model level
            $this->checklistLogger->cloned($checklistAtual, $checklist, [
                'source' => 'controller',
                'plane_id' => $plane->id,
            ]);

            DB::commit();
            flash(self::MSG_CLONE_SUCCESS)->success();

            return redirect()->route('checklists.index', ['kidId' => $request->kid_id]);
        } catch (Exception $e) {
            DB::rollBack();

            $this->checklistLogger->operationFailed('clone', $e, [
                'original_checklist_id' => $id ?? null,
                'kid_id' => $request->kid_id ?? null,
            ]);

            flash(self::MSG_CLONE_ERROR)->error();

            return redirect()->route('checklists.index');
        }
    }
}
