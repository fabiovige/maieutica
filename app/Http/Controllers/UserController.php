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

use Spatie\Permission\Models\Role as SpatieRole;

class UserController extends Controller
{
    public function __construct()
    {
        // Aplica a policy automaticamente para resource controller
        //$this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        $message = label_case('list users ').' | User:'.auth()->user()->name.'(ID: '.auth()->user()->id.') ';
        Log::info($message);

        $this->authorize('list users');

        $user = auth()->user();
        return view('users.index');
    }

    public function index_data()
    {

        /*if (auth()->user()->isSuperAdmin()) {
            $data = User::select('id', 'name', 'email', 'type', 'allow', 'role_id');
        } else {
            $data = User::select('id', 'name', 'email', 'type', 'allow', 'role_id');
            $data->where('created_by', '=', auth()->user()->id);
        }*/

        $data = User::select('id', 'name', 'email', 'type', 'allow');

        return Datatables::of($data)

            ->addColumn('action', function ($data) {
                if (request()->user()->can('update users') || request()->user()->can('create users')) {
                    $html = '<a class="btn btn-sm btn-secondary" href="'.route('users.edit', $data->id).'"><i class="bi bi-pencil"></i> Editar</a>';

                    return $html;
                }
            })

            ->editColumn('name', function ($data) {
                return $data->name;
            })

            ->editColumn('role', function ($data) {
                return '<span class="badge bg-primary"><i class="bi bi-shield-check"></i> '. $data->getRoleNames()->first() ?? ''.' </span>';
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
        $user = User::findOrFail($id);

        // Usa a política para verificar se o usuário pode atualizar
        $this->authorize('update', $user);

        try {
            //$user = User::findOrFail($id);

            /*if (auth()->user()->isSuperAdmin()) {
                $roles = Role::all();
            } elseif (auth()->user()->isAdmin()) {
                $roles = Role::where('created_by', '!=', Role::ROLE_SUPER_ADMIN)->get();
            } else {
                $roles = Role::where('created_by', '=', Auth::id())->get();
            }*/

            $roles = SpatieRole::all();

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
        /*
        if (auth()->user()->isSuperAdmin()) {
            $roles = Role::all();
        } elseif (auth()->user()->isAdmin()) {
            $roles = Role::where('created_by', '!=', Role::ROLE_SUPER_ADMIN)->get();
        } else {
            $roles = Role::where('created_by', '=', Auth::id())->get();
        }*/

        $roles = SpatieRole::all();

        $message = label_case('Create User ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['type'] = (! isset($request->type)) ? User::TYPE_I : $data['type'];

            // cadastra user com role_id = 3 (pais)
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'postal_code' => $request->cep,
                'street' => $request->logradouro,
                'number' => $request->numero,
                'complement' => $request->complemento,
                'neighborhood' => $request->bairro,
                'city' => $request->cidade,
                'state' => $request->estado,
                'password' => bcrypt('2024@mudar'), // Ou você pode gerar uma senha aleatória
                'role_id' => 3, // ROLE_PAIS (assumindo que 3 corresponde a ROLE_PAIS)
                'created_by' => auth()->user()->id,
                'allow' => (bool) isset($request->allow),
                'type' => $data['type'],
            ];

            $user = User::create($userData);
            Log::info('User created: '.$user->id.' created by: '.auth()->user()->id);


            $role = SpatieRole::find($data['role_id']);
            $user->syncRoles([]);
            $user->assignRole($role->name);
            $user->save();

            DB::commit();

            flash(self::MSG_CREATE_SUCCESS)->success();

            $message = label_case('Create User '.self::MSG_CREATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('users.index');

        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();

            $message = label_case('Create User '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            flash(self::MSG_CREATE_ERROR)->warning();
            return redirect()->back();
        }
    }

    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['type'] = (! isset($data['type'])) ? User::TYPE_I : $data['type'];

            $user = User::findOrFail($id);
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'postal_code' => $request->cep,
                'street' => $request->logradouro,
                'number' => $request->numero,
                'complement' => $request->complemento,
                'neighborhood' => $request->bairro,
                'city' => $request->cidade,
                'state' => $request->estado, // Ou você pode gerar uma senha aleatória
                'role_id' => $request->role_id, // ROLE_PAIS (assumindo que 3 corresponde a ROLE_PAIS)
                'updated_by' => auth()->user()->id,
                'allow' => (bool) isset($request->allow),
                'type' => $data['type'],
            ];

            $user->update($userData);

            $role = SpatieRole::find($data['role_id']);
            $user->syncRoles([]);
            $user->assignRole($role->name);
            $user->save();

            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();

            $message = label_case('Update User '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('users.edit', $id);

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
