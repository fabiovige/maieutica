<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResponsibleRequest;
use App\Models\Responsible;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ResponsibleController extends Controller
{
    public function index()
    {
        $message = label_case('Index Responsibles ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::debug($message);

        return view('responsibles.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Responsible::select('id', 'name', 'email', 'cell');
        } else {
            $data = Responsible::select('id', 'name', 'email', 'cell')->where('created_by', '=', auth()->user()->id);
        }

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('responsibles.update') || request()->user()->can('responsibles.store')) {

                    $html = '<a class="btn btn-sm btn-success" href="'.route('responsibles.edit', $data->id).'"><i class="bi bi-gear"></i> </a>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('email', function ($data) {
                return $data->email;
            })
            ->editColumn('cell', function ($data) {
                return $data->cell;
            })
            ->rawColumns(['action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        return view('responsibles.create');
    }

    public function store(ResponsibleRequest $request)
    {
        $responsible = new Responsible();
        $data = $request->all();

        // libera acesso ao pai
        if(isset($data['type'])) {

            $findUser = User::where('email', '=', $data['email'])->first();
            if(!$findUser) {
                $dataUser['name'] = $data['name'];
                $dataUser['password'] = bcrypt('password');
                $dataUser['email'] = $data['email'];
                $dataUser['created_by'] = Auth::id();
                $dataUser['type'] = User::TYPE_E;
                $user = User::create($dataUser);
                $role = Role::find(3);
                $user = $user->role()->associate($role);
                $user->save();
                $data['user_id'] = $user->id;
            }
        } else {
            $findUser = User::where('email', '=', $data['email'])->first();
            if($findUser) {
                $newPassword = bcrypt(hash(date('dmyhis')));
                $findUser->update(['password' => $newPassword]);
            }
        }

        $responsible->create($data);
        flash(self::MSG_CREATE_SUCCESS)->success();

        return redirect()->route('responsibles.index');
    }

    public function show(Responsible $responsible)
    {
        //
    }

    public function edit(Responsible $responsible)
    {
        try {
            return view('responsibles.edit', compact('responsible'));

        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Update Responsible ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(ResponsibleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $responsible = Responsible::findOrFail($id);

            if(isset($data['type'])) {
                $findUser = User::where('email', '=', $data['email'])->first();
                if(!$findUser) {
                    $dataUser['name'] = $data['name'];
                    $dataUser['password'] = bcrypt('password');
                    $dataUser['email'] = $data['email'];
                    $dataUser['created_by'] = Auth::id();
                    $dataUser['type'] = $data['type'];
                    $user = User::create($dataUser);
                    $role = Role::find(Role::ROLE_PAIS);
                    $user = $user->role()->associate($role);
                    $user->save();
                    $data['user_id'] = $user->id;
                    $responsible->update($data);
                }
                if($findUser) {
                    $user = User::findOrFail($findUser->id);
                    $user->update(['allow' => true]);
                }
                $responsible->update($data);
            } else {
                if($responsible->user) {
                    $user = User::findOrFail($responsible->user->id);
                    $user->update(['allow' => false]);
                }
            }


            $responsible->update($data);
            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();
            return redirect()->route('responsibles.index');

        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_UPDATE_ERROR)->warning();

            echo $message = label_case('Update Responsible '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            //return redirect()->back();
        }

    }

    public function destroy(Responsible $responsible)
    {
        $responsible->delete();
        flash(self::MSG_DELETE_SUCCESS)->success();
        return redirect()->route('responsibles.index');
    }
}
