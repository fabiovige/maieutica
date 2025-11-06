<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Ability;
use App\Models\Resource;
use App\Models\Role;
use App\Services\Logging\RoleLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission as SpatiePermission;

class RoleController extends Controller
{
    protected $roleLogger;

    public function __construct(RoleLogger $roleLogger)
    {
        $this->roleLogger = $roleLogger;
    }

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

        $this->roleLogger->listed([
            'search' => $request->input('search'),
            'total_results' => $roles->total(),
        ]);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

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
            $permissions = [];
            if ($request->has('permissions')) {
                $permissions = $request->input('permissions');
                $role->syncPermissions($permissions);

                // Log permissions sync
                $this->roleLogger->permissionsSynced($role, [], $permissions, [
                    'source' => 'controller',
                    'on_creation' => true,
                ]);
            }

            // Observer will log at model level
            $this->roleLogger->created($role, [
                'source' => 'controller',
                'permissions_count' => count($permissions),
            ]);

            // Confirma a transação
            DB::commit();
            flash(self::MSG_CREATE_SUCCESS)->success();

            // Redireciona para a página de listagem de roles
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();

            $this->roleLogger->operationFailed('store', $e);

            // Mensagem de erro
            flash(self::MSG_CREATE_ERROR)->error();

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

            $this->roleLogger->viewed($role, 'details');

            return view('roles.show', compact('role', 'abilities'));
        } catch (\Exception $e) {
            $this->roleLogger->operationFailed('show', $e, [
                'role_id' => $id,
            ]);

            flash(self::MSG_NOT_FOUND)->error();

            return redirect()->route('roles.index');
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        try {
            $permissions = SpatiePermission::orderBy('name')->get();

            $this->roleLogger->viewed($role, 'edit');

            return view('roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            $this->roleLogger->operationFailed('edit', $e, [
                'role_id' => $id,
            ]);

            flash(self::MSG_UPDATE_ERROR)->error();

            return redirect()->route('roles.index');
        }
    }

    public function update(RoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);

        DB::beginTransaction();
        try {
            // Get original data for change tracking
            $originalData = $role->only(['name']);
            $oldPermissions = $role->permissions->pluck('name')->toArray();

            // Atualiza as informações do role
            $data = $request->all();
            $data['updated_by'] = auth()->user()->id;

            $role->update([
                'name' => $data['name'],
            ]);

            // Sincroniza as permissões associadas ao role
            $newPermissions = $request->input('permissions') ?? [];
            $role->syncPermissions($newPermissions);

            // Track what changed
            $changes = [];
            $newData = $role->only(['name']);
            foreach ($newData as $key => $value) {
                if ($originalData[$key] != $value) {
                    $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
                }
            }

            // Log permissions sync
            if ($oldPermissions != $newPermissions) {
                $this->roleLogger->permissionsSynced($role, $oldPermissions, $newPermissions, [
                    'source' => 'controller',
                ]);
            }

            // Observer will log at model level
            if (!empty($changes)) {
                $this->roleLogger->updated($role, $changes, [
                    'source' => 'controller',
                    'permissions_changed' => $oldPermissions != $newPermissions,
                ]);
            }

            // Confirma a transação
            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();

            // Redireciona para a página de edição
            return redirect()->route('roles.edit', $id);
        } catch (\Exception $e) {
            // Se der erro, desfaz a transação
            DB::rollBack();

            $this->roleLogger->operationFailed('update', $e, [
                'role_id' => $id,
            ]);

            // Flash message
            flash(self::MSG_UPDATE_ERROR)->error();

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
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                $this->roleLogger->accessDenied('delete', $role, [
                    'reason' => 'Role tem usuários vinculados',
                    'users_count' => $usersCount,
                ]);

                throw new \Exception('Não é possível mover para lixeira, pois existem usuários vinculados a este perfil.');
            }

            // Envia para lixeira (soft delete)
            $role->delete();

            // Observer will log at model level
            $this->roleLogger->deleted($role, [
                'source' => 'controller',
            ]);

            // Confirma a transação
            DB::commit();
            flash('Perfil movido para a lixeira com sucesso.')->success();

            // Redireciona para a listagem de roles
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();
            flash($e->getMessage())->error();

            $this->roleLogger->operationFailed('destroy', $e, [
                'role_id' => $role->id,
            ]);

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

        $this->roleLogger->trashViewed([
            'total_trashed' => $roles->total(),
        ]);

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

            // Observer will log at model level
            $this->roleLogger->restored($role, [
                'source' => 'controller',
            ]);

            DB::commit();

            flash('Perfil restaurado com sucesso.')->success();

            return redirect()->route('roles.trash');
        } catch (\Exception $e) {
            DB::rollBack();

            flash('Erro ao restaurar perfil: ' . $e->getMessage())->warning();

            $this->roleLogger->operationFailed('restore', $e, [
                'role_id' => $id,
            ]);

            return redirect()->back();
        }
    }
}
