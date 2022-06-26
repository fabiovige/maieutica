<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Models\Kid;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class KidsController extends Controller
{
    public function index(Request $request)
    {
        $message = label_case('Index Kids ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::debug($message);

        return view('kids.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Kid::select('id', 'name', 'birth_date');
        } else {
            $data = Kid::select('id', 'name', 'birth_date')
                ->where('created_by', '=', auth()->user()->id)
                ->orWhere('user_id', '=', auth()->user()->id);
        }

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('kids.show')) {
                    $html = '<a class="btn btn-sm btn-success" href="'.route('kids.show', $data->id).'"><i class="bi bi-gear"></i> Gerenciar</a>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('birth_date', function ($data) {
                return Carbon::createFromFormat('Y-m-d', $data->birth_date)->format('d/m/Y');
            })
            ->rawColumns(['name', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Kids').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('kids.create');
    }

    public function store(KidRequest $request)
    {
        try {
            $data = $request->all();
            $data['birth_date'] = Carbon::createFromFormat('d/m/Y', $data['birth_date'])->format('Y-m-d');
            $data['created_by'] = Auth::id();
            Kid::create($data);
            flash(self::MSG_CREATE_SUCCESS)->success();
            $message = label_case('Store Kids '.self::MSG_CREATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_CREATE_ERROR)->warning();
            $message = label_case('Store Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->route('kids.index');
        }
    }

    public function show($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->birth_date = Carbon::createFromFormat('Y-m-d', $kid->birth_date)->format('d/m/Y');
            $message = label_case('Show Kids ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('kids.show', [
                'kid' => $kid,
            ]);
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Show Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->birth_date = Carbon::createFromFormat('Y-m-d', $kid->birth_date)->format('d/m/Y');
            $message = label_case('Edit Kids ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('kids.edit', [
                'kid' => $kid,
            ]);
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(KidRequest $request, $id)
    {
        try {
            $data = $request->all();
            $data['birth_date'] = Carbon::createFromFormat('d/m/Y', $data['birth_date'])->format('Y-m-d');
            $data['updated_by'] = Auth::id();
            $kid = Kid::findOrFail($id);
            $kid->update($data);
            flash(self::MSG_UPDATE_SUCCESS)->success();
            $message = label_case('Update Kids '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return redirect()->route('kids.show', $id);
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->deleted_by = Auth::id();
            $kid->update();
            $kid->delete();
            flash(self::MSG_DELETE_SUCCESS)->success();
            $message = label_case('Destroy Kids '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_DELETE_ERROR)->warning();
            $message = label_case('Destroy Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
