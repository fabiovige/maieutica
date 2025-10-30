<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Ability;
use App\Models\Resource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission as SpatiePermission;

class RoleController extends Controller
{
    const MSG_CREATE_SUCCESS = 'Perfil criado com sucesso!';
    const MSG_CREATE_ERROR = 'Erro ao criar perfil.';
    const MSG_UPDATE_SUCCESS = 'Perfil atualizado com sucesso!';
    const MSG_UPDATE_ERROR = 'Erro ao atualizar perfil.';
    const MSG_DELETE_SUCCESS = 'Perfil excluído com sucesso!';
    const MSG_DELETE_ERROR = 'Erro ao excluir perfil.';
    const MSG_NOT_FOUND = 'Perfil não encontrado.';

    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query();

        // Filtro de busca geral (nome do perfil ou permissão)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('permissions', function($permQuery) use ($search) {
                      $permQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $roles = $query->with('permissions')->orderBy('name')->paginate(5);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $message = label_case('Create Role ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        $permissions = SpatiePermission::orderBy('name')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);

        DB::beginTransaction();
        try {
            // Obtém os dados do request e adiciona o ID do usuário que criou o role
            $data = $request->all();
            $data['created_by'] = auth()->user()->id;

            // Cria o role com os dados fornecidos
            $role = Role::create([
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
        $role = Role::findOrFail($id);
        $this->authorize('view', $role);

        try {
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
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        try {
            $message = label_case('Edit Role ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $permissions = SpatiePermission::orderBy('name')->get();

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
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        DB::beginTransaction();
        try {
            // Atualiza as informações do role
            $data = $request->all();
            $data['updated_by'] = auth()->user()->id;

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

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        DB::beginTransaction();

        try {
            // Verifica se existem usuários vinculados ao papel
            if ($role->users()->count() > 0) {
                throw new \Exception('Não é possível mover para lixeira, pois existem usuários vinculados a este perfil.');
            }

            // Envia para lixeira (soft delete)
            $role->delete();

            // Confirma a transação
            DB::commit();
            flash('Perfil movido para a lixeira com sucesso.')->success();

            // Log de sucesso
            $message = label_case('Role moved to trash. ') . ' | Deleted Role: ' . $role->name . ' (ID: ' . $role->id . ')';
            Log::notice($message);

            // Redireciona para a listagem de roles
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();
            flash($e->getMessage())->error();

            // Log de erro
            $message = label_case('Error while deleting role: ' . $e->getMessage()) . ' | User: ' . auth()->user()->name . ' (ID: ' . auth()->id() . ')';
            Log::error($message);

            // Redireciona de volta com erro
            return redirect()->back();
        }
    }

    public function trash()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::onlyTrashed()
            ->with('permissions')
            ->orderBy('deleted_at', 'desc')
            ->paginate(5);

        $message = label_case('View Trash Roles') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        return view('roles.trash', compact('roles'));
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $role = Role::onlyTrashed()->findOrFail($id);

            $this->authorize('update', $role);

            // Restaura o perfil da lixeira
            $role->restore();

            DB::commit();

            flash('Perfil restaurado com sucesso.')->success();

            $message = label_case('Role restored. ') . ' | Restored Role: ' . $role->name . ' (ID: ' . $role->id . ')';
            Log::notice($message);

            return redirect()->route('roles.trash');
        } catch (\Exception $e) {
            DB::rollBack();

            flash('Erro ao restaurar perfil: ' . $e->getMessage())->warning();

            $message = label_case('Error while restoring role: ' . $e->getMessage()) . ' | User: ' . auth()->user()->name . ' (ID: ' . auth()->id() . ')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
