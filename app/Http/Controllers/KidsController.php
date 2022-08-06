<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Models\Kid;
use App\Models\User;
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

//        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
//            $data = Kid::with('user')->select('id', 'name', 'birth_date', 'user_id');
//        } else {
//            $data = Kid::with('user')->select('id', 'name', 'birth_date')->where('created_by', '=', auth()->user()->id);
//        }

        return view('kids.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Kid::with('user')->select('id', 'name', 'birth_date', 'user_id');
        } else {
            $data = Kid::with('user')->select('id', 'name', 'birth_date', 'user_id')->where('created_by', '=', auth()->user()->id);
        }

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('kids.update') || request()->user()->can('kids.store')) {
                    $html = '<a class="btn btn-sm btn-success" href="'.route('kids.show', $data->id).'"><i class="bi bi-gear"></i></a>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('birth_date', function ($data) {
//                $now = Carbon::now();
//                $months = ($now->diffInMonths($data->birth_date) == 0) ? 1 : $now->diffInMonths($data->birth_date);
                return $data->birth_date;
            })
            ->editColumn('checklists', function ($data) {
                return $data->checklists->count();
            })
            ->editColumn('user_id', function ($data) {
                return $data->user->name;
            })
            ->rawColumns(['name', 'checklists', 'user_id','action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Kids').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        $users = User::all('id', 'name');
        return view('kids.create', compact('users'));
    }

    public function store(KidRequest $request)
    {
        try {
            $message = label_case('Store Kids '.self::MSG_CREATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['created_by'] = Auth::id();
            Kid::create($data);
            flash(self::MSG_CREATE_SUCCESS)->success();
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
            $message = label_case('Show Kids ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $kid = Kid::findOrFail($id);
            return view('kids.show', [
                'kid' => $kid,
            ]);
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Show Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        try {
            $message = label_case('Edit Kids ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $kid = Kid::findOrFail($id);
            $users = User::all('id', 'name');
            return view('kids.edit', compact('kid', 'users'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Update Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(KidRequest $request, $id)
    {
        try {
            $message = label_case('Update Kids '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $data = $request->all();
            $data['updated_by'] = Auth::id();
            $kid = Kid::findOrFail($id);
            $kid->update($data);
            flash(self::MSG_UPDATE_SUCCESS)->success();

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
            $message = label_case('Destroy Kids '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            $kid = Kid::findOrFail($id);
            $kid->deleted_by = Auth::id();
            $kid->update();
            $kid->delete();
            flash(self::MSG_DELETE_SUCCESS)->success();
            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Destroy Kids '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
