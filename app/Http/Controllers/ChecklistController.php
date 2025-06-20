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
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChecklistController extends Controller
{

    protected $checklistService;

    public function __construct(ChecklistService $checklistService)
    {
        $this->checklistService = $checklistService;
    }

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Checklist::class);

            $message = label_case('Index Checklists ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::debug($message);

            $queryChecklists = Checklist::getChecklists();

            $kid = $request->kidId ? Kid::findOrFail($request->kidId) : null;

            if ($request->kidId || auth()->user()->hasRole('pais')) {
                if ($kid) {
                    $queryChecklists->where('kid_id', $request->kidId);
                } elseif (auth()->user()->hasRole('pais')) {

                    $kids = Kid::where('responsible_id', auth()->user()->id)->pluck('id');
                    $queryChecklists->whereIn('kid_id', $kids);
                }
            } elseif (auth()->user()->hasRole('professional')) {

                $professionalId = auth()->user()->professional->first()->id;
                $queryChecklists->whereHas('kid.professionals', function ($query) use ($professionalId) {
                    $query->where('professional_id', $professionalId);
                });
            }


            $checklists = $queryChecklists->with('competences')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            foreach ($checklists as $checklist) {
                $checklist->developmentPercentage = $this->percentualDesenvolvimento($checklist->id);
            }

            $data = [
                'checklists' => $checklists,
                'kid' => $kid
            ];

            return view('checklists.index', $data);
        } catch (Exception $e) {
            dd($e->getMessage());
            flash($e->getMessage())->warning();
            $message = label_case('Index Checklists ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function create()
    {
        $this->authorize('create', Checklist::class);

        $message = label_case('Create Checklist ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);
        $kids = Kid::getKids();

        return view('checklists.create', compact('kids'));
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
                            $notes[$competence_id] = ['note' => $chechlistCompetente ? $chechlistCompetente['note'] : 0];
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

        try {
            $message = label_case('Edit Checklist ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return view('checklists.show', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            $message = label_case('Update Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $this->authorize('update', Checklist::findOrFail($id));

        try {
            $message = label_case('Edit Checklist ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $checklist = Checklist::findOrFail($id);

            return view('checklists.edit', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(ChecklistRequest $request, $id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('update', $checklist);

        try {
            $message = label_case('Update Checklists ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $data = $request->all();
            $data['updated_by'] = Auth::id();
            // Permitir atualização manual da situação (aberto/fechado)
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

            $message = label_case('Update Checklists ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('delete', $checklist);

        try {
            $message = label_case('Destroy Checklist ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);
            if ($checklist->planes()->exists()) {
                foreach ($checklist->planes as $plane) {
                    $plane->deleted_by = Auth::id(); // Atribui o usuário que deletou
                    $plane->save(); // Salva as alterações no banco de dados
                    $plane->delete(); // Realiza a exclusão (soft delete, se for o caso)
                }
            }

            // Exclui o Checklist
            $checklist->deleted_by = Auth::id();
            $checklist->save();
            $checklist->delete();

            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            $message = label_case('Destroy Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
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

    public function clonarChecklist(Request $request, $checklistId = null)
    {
        $this->authorize('create', Checklist::class);

        if (! auth()->user()->can('create checklists')) {
            flash('Você não tem permissão para clonar checklists.')->warning();

            return redirect()->route('checklists.index');
        }

        try {
            DB::beginTransaction();

            $checklistAtual = Checklist::where('situation', 'a')
                ->when($request->kid_id, function ($query) use ($request) {
                    return $query->where('kid_id', $request->kid_id);
                })
                ->when($checklistId, function ($query) use ($checklistId) {
                    return $query->where('id', $checklistId);
                })
                ->firstOrFail();


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
                    $notes[$competence_id] = ['note' => $chechlistCompetente['note']];
                }
                $checklist->competences()->syncWithoutDetaching($notes);
            }

            DB::commit();
            flash(self::MSG_CLONE_SUCCESS)->success();

            return redirect()->route('checklists.index', ['kidId' => $request->kid_id]);
        } catch (Exception $e) {
            DB::rollBack();

            $message = label_case('Fill Checklist ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';

            log($message, $e->getMessage());
            flash(self::MSG_CLONE_ERROR)->success();

            return redirect()->route('checklists.index');
        }
    }
}
