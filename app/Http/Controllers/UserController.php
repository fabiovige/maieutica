<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $message = label_case('Index User ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('users.index');
    }

    public function index_data()
    {

        if (auth()->user()->isSuperAdmin()) {
            $data = User::select('id', 'name', 'email', 'type', 'allow', 'role_id');
        } else {
            $data = User::select('id', 'name', 'email', 'type', 'allow', 'role_id');
            $data->where('created_by', '=', auth()->user()->id);
        }

        return Datatables::of($data)

            ->addColumn('action', function ($data) {
                if (request()->user()->can('users.update') || request()->user()->can('users.create')) {
                    $html = '<a class="btn btn-sm btn-success" href="'.route('users.edit', $data->id).'"><i class="bi bi-gear"></i> </a>';

                    return $html;
                }
            })

            ->editColumn('name', function ($data) {
                return $data->name;
            })

            ->editColumn('role', function ($data) {
                $role = '<span class="badge bg-primary"><i class="bi bi-shield-check"></i> '.$data->role->name.' </span>';

                return $role;
            })

            ->editColumn('email', function ($data) {
                return $data->email;
            })

            ->editColumn('allow', function ($data) {

                if ($data->allow) {
                    $html = '<span class="badge bg-primary"><i class="bi bi-emoji-smile"></i> Sim </span>';
                } else {
                    $html = '<span class="badge bg-info"><i class="bi bi-emoji-frown"></i> Não </span>';
                }

                return $html;
            })
            ->rawColumns(['name', 'role', 'type', 'allow', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);

            if (auth()->user()->isSuperAdmin()) {
                $roles = Role::all();
            } elseif (auth()->user()->isAdmin()) {
                $roles = Role::where('created_by', '!=', Role::ROLE_SUPER_ADMIN)->get();
            } else {
                $roles = Role::where('created_by', '=', Auth::id())->get();
            }

            $message = label_case('Edit User ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('users.edit', compact('user', 'roles'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();

            $message = label_case('Edit User '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            $roles = Role::all();

            $message = label_case('Show User ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('users.show', compact('user', 'roles'));
        } catch (Exception $e) {
            $message = self::MSG_NOT_FOUND;

            flash($message)->warning();

            $message = label_case('Show User '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function create()
    {
        if (auth()->user()->isSuperAdmin()) {
            $roles = Role::all();
        } elseif (auth()->user()->isAdmin()) {
            $roles = Role::where('created_by', '!=', Role::ROLE_SUPER_ADMIN)->get();
        } else {
            $roles = Role::where('created_by', '=', Auth::id())->get();
        }

        $message = label_case('Create User ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['password'] = bcrypt('password');
            $data['created_by'] = Auth::id();
            $user = User::create($data);

            $role = Role::find($data['role_id']);
            $user = $user->role()->associate($role);
            $user->save();

            DB::commit();

            flash(self::MSG_CREATE_SUCCESS)->success();

            $message = label_case('Create User '.self::MSG_CREATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('users.index');

        } catch (Exception $e) {
            DB::rollBack();

            flash(self::MSG_CREATE_ERROR)->warning();

            $message = label_case('Create User '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $data['type'] = (! isset($data['type'])) ? User::TYPE_I : $data['type'];
            $data['allow'] = (bool) isset($data['allow']);

            $user = User::findOrFail($id);
            $data['updated_by'] = Auth::id();
            $user->update($data);

            $role = Role::find($data['role_id']);
            $user = $user->role()->associate($role);
            $user->save();

            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();

            $message = label_case('Update User '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('users.index');

        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_UPDATE_ERROR)->warning();

            $message = label_case('Update User '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {

            if (auth()->user()->role_id == $user->id) {
                $message = label_case('Destroy Self Users '.self::MSG_DELETE_USER_SELF).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
                Log::alert($message);
                throw new Exception(self::MSG_DELETE_USER_SELF);
            }
            $user->deleted_by = Auth::id();
            $user->update();
            $user->delete();
            DB::commit();

            flash(self::MSG_DELETE_SUCCESS)->success();

            $message = label_case('Destroy Users '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('users.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_DELETE_ERROR)->warning();

            $message = label_case('Destroy Users '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function pdf($id)
    {
        try {
            $user = User::findOrFail($id);

            $pdf = PDF::loadView('users.show', compact('user'));

            $message = label_case('PDF Users Teste ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return $pdf->download('user.pdf');
        } catch (Exception $e) {
            flash(self::MSG_DELETE_ERROR)->warning();

            $message = label_case('Destroy Users '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
