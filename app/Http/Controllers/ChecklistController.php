<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\CompetenceDescription;
use App\Models\Kid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ChecklistController extends Controller
{
    public function index()
    {
        $message = label_case('Index Checklists ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::debug($message);

        return view('checklists.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Checklist::with('kid')->select('id', 'level', 'situation', 'kid_id', 'created_at');
        } else {
            $data = Checklist::with('kid')->select('id', 'level', 'situation', 'kid_id', 'created_at')->where('created_by', '=', auth()->user()->id);
        }

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('checklists.update') || request()->user()->can('checklists.store')) {
                    $html = '<a class="btn btn-sm btn-success" href="'.route('checklists.show', $data->id).'"><i class="bi bi-gear"></i></a>';

                    return $html;
                }
            })
            ->editColumn('kid_id', function ($data) {
                return $data->kid->name;
            })
            ->editColumn('level', function ($data) {
                return $data->level;
            })
            ->editColumn('situation', function ($data) {
                return Checklist::SITUATION[$data->situation];
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d/m/Y') ;
            })
            ->rawColumns(['kid_id','level','situation','created_at','action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        $kids = Kid::all('id', 'name');

        return view('checklists.create', compact('kids'));
    }

    public function store(ChecklistRequest $request)
    {
        try {
            $message = label_case('Store Checklists '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['created_by'] = Auth::id();

            Checklist::create($data);

            flash(self::MSG_UPDATE_SUCCESS)->success();
            return redirect()->route('checklists.index');

        } catch (Exception $e) {

            $message = label_case('Create Checklists '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();
            return redirect()->back();
        }
    }

    public function show($id)
    {
        try {
            $message = label_case('Show Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $checklist = Checklist::findOrFail($id);
            return view('checklists.show', compact('checklist'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Show Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        try {
            $checklist = Checklist::findOrFail($id);
            $checklist->created_at = Carbon::createFromFormat('Y-m-d H:i:s', $checklist->created_at)->format('d/m/Y H:i');

            $message = label_case('Edit Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('checklists.edit', [
                'checklist' => $checklist,
            ]);
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(ChecklistRequest $request, $id)
    {
        try {
            $message = label_case('Update Checklists '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['updated_by'] = Auth::id();
            $checklist = Checklist::findOrFail($id);
            $checklist->update($data);

            flash(self::MSG_UPDATE_SUCCESS)->success();
            return redirect()->route('checklists.show', $id);

        } catch (Exception $e) {

            $message = label_case('Update Checklists '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            flash(self::MSG_UPDATE_ERROR)->warning();
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $message = label_case('Destroy Checklist '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $checklist = Checklist::findOrFail($id);
            $checklist->deleted_by = Auth::id();
            $checklist->update();
            $checklist->delete();
            flash(self::MSG_DELETE_SUCCESS)->success();
            return redirect()->route('checklists.index');
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Destroy Checkilist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function fill(Request $request)
    {
        try {
            $message = label_case('Fill Checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);
            $kids = Kid::all('id', 'name');
            return view('checklists.fill', compact('kids'));

        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Fill Checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}