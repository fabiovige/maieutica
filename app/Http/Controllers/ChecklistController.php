<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Models\ChecklistRegister;
use App\Models\Competence;
use App\Models\Kid;
use App\Models\Plane;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ChecklistController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Checklist::class);

        $message = label_case('Index Checklists ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::debug($message);

        $checklists = Checklist::getChecklists()->get();

        return view('checklists.index', compact('checklists'));
    }

    public function index_data()
    {
        /*
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Checklist::with('kid')->select('id', 'level', 'situation', 'kid_id', 'created_at');
        } else {
            $data = Checklist::with('kid')->select('id', 'level', 'situation', 'kid_id', 'created_at');
            $data->where('created_by', '=', auth()->user()->id);
        }
        */

        $data = Checklist::getChecklists();

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                $user = request()->user();

                $html = '<div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';

                    // Adiciona o botão de visualizar se o usuário tiver permissão de visualizar checklists
                    /*if ($user->can('view checklists')) {
                        $html .= '<li><a class="dropdown-item" href="'.route('checklists.show', $data->id).'">
                                    <i class="bi bi-eye"></i> Visualizar
                                </a></li>';
                    }*/

                    // Adiciona o botão de editar se o usuário tiver permissão de editar checklists
                    if ($user->can('edit checklists')) {
                        $html .= '<li><a class="dropdown-item" href="'.route('checklists.edit', $data->id).'">
                                    <i class="bi bi-pencil"></i> Anotações
                                </a></li>';
                    }

                    // Adiciona o botão de avaliação se o usuário tiver permissão de preencher checklists (fill)
                    if ($user->can('fill checklists')) {
                        $html .= '<li><a class="dropdown-item" href="'.route('checklists.fill', $data->id).'">
                                    <i class="bi bi-check2-square"></i> Aplicar avaliação
                                </a></li>';
                    }

                    // Adiciona o botão de avaliação se o usuário tiver permissão de preencher checklists (fill)
                    if ($user->can('fill checklists')) {
                        $html .= '<li><a class="dropdown-item" href="'.route('kids.show', [$data->kid->id]).'">
                                    <i class="bi bi-check2-square"></i> Planos
                                </a></li>';
                    }

                $html .= '</ul></div>';

                return $html;
            })
            ->editColumn('kid_id', function ($data) {
                return $data->kid->name;
            })
            ->editColumn('level', function ($data) {
                return $data->level;
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d/m/Y');
            })
            ->rawColumns(['kid_id', 'level', 'created_at', 'situation', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', Checklist::class);

        $message = label_case('Create Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);
        $kids = Kid::getKids();

        return view('checklists.create', compact('kids'));
    }

    public function store(ChecklistRequest $request)
    {
        $this->authorize('create', Checklist::class);

        try {
            $message = label_case('Store Checklists '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['created_by'] = Auth::id();

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

            foreach ($arrLevel as $c => $level) {
                $components = Competence::where('level_id', '=', $level)->pluck('id')->toArray();
                $notes = [];
                // competences
                foreach ($components as $c => $v) {
                    $notes[$v] = ['note' => 0];
                }
                $checklist->competences()->syncWithoutDetaching($notes);
            }

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {
            dd($e->getMessage());
            $message = label_case('Create Checklists '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
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
            $message = label_case('Edit Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('checklists.show', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            $message = label_case('Update Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit checklists');

        try {
            $message = label_case('Edit Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $checklist = Checklist::findOrFail($id);

            return view('checklists.edit', [
                'checklist' => $checklist,
            ]);
        } catch (\Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(ChecklistRequest $request, $id)
    {
        $checklist = Checklist::findOrFail($id);
        $this->authorize('update', $checklist);

        try {
            $message = label_case('Update Checklists '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['updated_by'] = Auth::id();
            $checklist->update($data);

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('checklists.index');
        } catch (\Exception $e) {

            $message = label_case('Update Checklists '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
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
            $message = label_case('Destroy Checklist '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
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
            $message = label_case('Destroy Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();
            return redirect()->back();
        }
    }


    public function fill($id)
    {
        try {
            $message = label_case('Fill Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);
            $checklist = Checklist::findOrFail($id);
            $data = [
                'checklist_id' => $id,
                'level_id' => $checklist->level,
                'created_at' => $checklist->created_at->format('d/m/Y').' Cod. '.$id,
                'kid' => $checklist->kid,
            ];
            return view('checklists.fill', $data);
        } catch (\Exception $e) {
            $message = label_case('Fill Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            dd($e->getMessage());
            flash(self::MSG_NOT_FOUND)->warning();
            //return redirect()->back();
        }
    }

    /*public function register(Request $request)
    {
        $data = $request->all();
        $checklistRegister = ChecklistRegister::where('checklist_id', $request->checklist_id)->where('competence_description_id', $request->competence_description_id);
        if ($checklistRegister->count()) {
            $id = $checklistRegister->first()->id;
            $cr = ChecklistRegister::findOrFail($id);
            $cr->update($data);
        } else {
            $checklistRegister->create($data);
        }
    }*/

    public function chart($id)
    {
        try {
            $message = label_case('Esfera Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
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
            $message = label_case('Fill Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
