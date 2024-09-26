<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Ability;
use App\Models\Resource;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function index()
    {
        $message = label_case('Index Role ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        return view('roles.index');
    }

    public function index_data()
    {
        /*if (auth()->user()->isSuperAdmin()) {
            $data = ModelsRole::select('id', 'name');
        } else {
            $data = Role::select('id', 'name')->where('created_by', '=', auth()->user()->id);
        }
        */

        $data = SpatieRole::where('name', '!=', 'superadmin');

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('edit roles')) {
                    $html = '<a class="btn btn-sm btn-success" href="' . route('roles.edit', $data->id) . '"><i class="bi bi-pencil"></i> Editar</a>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->rawColumns(['name', 'action'])
            //->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Role ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        //$resources = Resource::with('abilities')->orderBy('created_at')->get();
        $permissions = SpatiePermission::all();

        return view('roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        DB::beginTransaction();
        try {
            // Obtém os dados do request e adiciona o ID do usuário que criou o role
            $data = $request->all();
            $data['created_by'] = auth()->user()->id;

            // Cria o role com os dados fornecidos
            $role = SpatieRole::create([
                'name' => $data['name'],
            ]);

            // Sincroniza as permissões com o role criado
            if ($request->has('permissions')) {
                $role->syncPermissions($request->input('permissions'));
            }

            // Confirma a transação
            DB::commit();
            flash(self::MSG_CREATE_SUCCESS)->success();

            // Log de sucesso
            $message = label_case('Store Role ' . self::MSG_CREATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::notice($message);

            // Redireciona para a página de listagem de roles
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();

            // Mensagem de erro e log do erro
            flash(self::MSG_CREATE_ERROR)->error();
            $message = label_case('Store Role ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            // Redireciona de volta para a página de criação, com os dados antigos
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        try {
            $role = $this->role->findOrFail($id);
            $resources = Resource::with('abilities')->orderBy('created_at')->get();
            $abilities = Ability::assocAbilities($role, $resources);
            $message = label_case('Show Role ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return view('roles.show', compact('role', 'abilities'));
        } catch (\Exception $e) {
            flash(self::MSG_NOT_FOUND)->error();

            $message = label_case('Show Role ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('roles.index');
        }
    }

    public function edit($id)
    {
        try {
            $role = SpatieRole::findOrFail($id);
            $message = label_case('Edit Role ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $permissions = SpatiePermission::all();

            $abilities = [];

            return view('roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->error();

            $message = label_case('Edit Role ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('roles.index');
        }
    }

    public function update(RoleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Busca o role pelo ID
            $role = SpatieRole::findOrFail($id);

            // Atualiza as informações do role
            $data = $request->all();
            $data['updated_by'] = auth()->user()->id;  // Adiciona o usuário que fez a atualização

            // Atualiza o nome do role, ou outras informações pertinentes

            $role->update([
                'name' => $data['name'],
            ]);

            // Sincroniza as permissões associadas ao role
            $role->syncPermissions($request->input('permissions'));

            // Confirma a transação
            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();

            // Loga a ação de sucesso
            $message = label_case('Update Role ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::notice($message);

            // Redireciona para a página de edição
            return redirect()->route('roles.edit', $id);
        } catch (\Exception $e) {
            // Se der erro, desfaz a transação
            DB::rollBack();

            // Flash message e log do erro
            flash(self::MSG_UPDATE_ERROR)->error();
            $message = label_case('Update Role ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            // Redireciona de volta para a página anterior
            return redirect()->back();
        }
    }

    public function destroy(SpatieRole $role)
    {
        DB::beginTransaction();

        try {
            // Verifica se o usuário autenticado está tentando excluir seu próprio papel
            if (auth()->user()->hasRole($role->name)) {
                throw new \Exception(self::MSG_DELETE_ROLE_SELF);
            }

            // Verifica se existem usuários vinculados ao papel
            if ($role->users()->count() > 0) {
                throw new \Exception('Não é possível excluir o papel, pois existem usuários vinculados a ele.');
            }

            // Registra quem está deletando o papel
            $role->save(); // Usa save para garantir a atualização

            // Exclui o papel
            $role->delete();

            // Confirma a transação
            DB::commit();
            flash(self::MSG_DELETE_SUCCESS)->success();

            // Log de sucesso
            $message = label_case('Destroy Role ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::notice($message);

            // Redireciona para a listagem de roles
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();
            flash($e->getMessage())->error();

            // Log de erro
            $message = label_case('Destroy Role ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            // Redireciona de volta com erro
            return redirect()->back();
        }
    }
}
