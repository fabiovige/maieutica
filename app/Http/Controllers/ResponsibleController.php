<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResponsibleRequest;
use App\Models\Responsible;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ResponsibleController extends Controller
{
    public function index()
    {
        $message = label_case('Index Responsibles ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::debug($message);

        return view('responsibles.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Responsible::select('id', 'name', 'email', 'cell');
        } else {
            $data = Responsible::select('id', 'name', 'email', 'cell');
            $data->where('created_by', '=', auth()->user()->id);
            $data->orWhere('user_id', '=', auth()->user()->id);
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
        $data['created_by'] = Auth::id();

        $findUser = User::where('email', '=', $data['email']);
        if ($data['allow']) {

            if ($findUser->count() == 0) {
                $dataUser['name'] = $data['name'];
                $dataUser['password'] = bcrypt('password');
                $dataUser['email'] = $data['email'];
                $dataUser['created_by'] = Auth::id();
                $dataUser['allow'] = false;
                $user = User::create($dataUser);
                $role = Role::find(3);
                $user = $user->role()->associate($role);
                $user->save();
                $data['user_id'] = $user->id;
            }
        }

        $responsible->create($data);
        flash(self::MSG_CREATE_SUCCESS)->success();

        return redirect()->route('responsibles.index');
    }

    public function edit(Responsible $responsible)
    {
        try {
            $allow = ($responsible->user()->count() == 0 ? false : $responsible->user->allow);

            return view('responsibles.edit', [
                'responsible' => $responsible,
                'allow' => $allow,
            ]);

        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Update Responsible '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(ResponsibleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['updated_by'] = Auth::id();
            $responsible = Responsible::findOrFail($id);
            $user = User::where('email', '=', $data['email']);

            if (isset($data['allow'])) {

                if ($user->count() > 0) {
                    $this->emailDuplicate($data['email']);
                    $dataUser['allow'] = true;
                    $user->first()->update($dataUser);
                } else {
                    // busca na lixeira e restaura
                    $userTrash = User::where('email', '=', $data['email'])->withTrashed();
                    if ($userTrash->count()) {
                        $userTrash->restore();
                    } else {
                        // verifica se ja existe esse e-mail
                        $this->emailDuplicate($data['email']);
                        $dataUser['name'] = $data['name'];
                        $dataUser['password'] = bcrypt('password');
                        $dataUser['email'] = $data['email'];
                        $dataUser['created_by'] = Auth::id();
                        $dataUser['allow'] = true;
                        $user = User::create($dataUser);
                        $role = Role::find(Role::ROLE_PAIS);
                        $user = $user->role()->associate($role);
                        $user->save();
                        $data['user_id'] = $user->id;
                    }
                }
            } else {
                if ($user->count() > 0) {
                    $dataUser['allow'] = false;
                    $user->first()->update($dataUser);
                }
            }
            $responsible->update($data);
            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('responsibles.index');

        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Responsible '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }

    }

    private function emailDuplicate($email)
    {
        $userEmail = User::where('email', '=', $email)->get();
        if ($userEmail->count() > 0) {
            $msg = sprintf(self::MSG_ALREADY_EXISTS, $email);
            flash($msg)->warning();

            return redirect()->route('responsibles.index');
        }
    }

    public function destroy(Responsible $responsible)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($responsible->user_id);
            $user->deleted_by = Auth::id();
            $responsible->deleted_by = Auth::id();
            $responsible->delete();
            $user->delete();
            DB::commit();
            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('responsibles.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_DELETE_SUCCESS)->warning();
            $message = label_case('Delete Responsible '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
