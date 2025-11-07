<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\Logging\UserLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Professional;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userLogger;

    public function __construct(UserLogger $userLogger)
    {
        $this->userLogger = $userLogger;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'professional']);

        // Filtro de busca geral (nome, email ou perfil)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('roles', function($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $users = $query->paginate(self::PAGINATION_DEFAULT);

        $this->userLogger->listed([
            'search' => $request->input('search'),
            'total_results' => $users->total(),
        ]);

        return view('users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        try {
            $roles = SpatieRole::orderBy('name')->get();

            $this->userLogger->viewed($user, 'edit');

            return view('users.edit', compact('user', 'roles'));
        } catch (Exception $e) {
            $this->userLogger->operationFailed('edit', $e, ['user_id' => $id]);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function update(UserRequest $request, User $user)
    {
        //$user = User::findOrFail($id);
        $this->authorize('update', $user);

        DB::beginTransaction();
        try {
            $data = $request->all();

            $data['type'] = (! isset($data['type'])) ? User::TYPE_I : $data['type'];

            // Get original data for change tracking
            $originalData = $user->only(['name', 'email', 'phone', 'postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'allow', 'type']);

            // Track old roles before changes
            $oldRoles = $user->roles->pluck('name')->toArray();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->postal_code = $request->cep;
            $user->street = $request->logradouro;
            $user->number = $request->numero;
            $user->complement = $request->complemento;
            $user->neighborhood = $request->bairro;
            $user->city = $request->cidade;
            $user->state = $request->estado;
            //$user->role_id = $request->role_id;
            $user->updated_by = auth()->user()->id;
            $user->allow = (bool) isset($request->allow);
            $user->type = $data['type'];


            $user->save();

            // Proteção: Não permite mudar roles se o user está vinculado a um Professional
            if ($user->professional->count() > 0) {
                // User vinculado a professional - mantém role 'profissional' fixo
                if (!$user->hasRole('profissional')) {
                    $user->assignRole('profissional');
                }
                // Log tentativa de alterar role bloqueada
                $this->userLogger->accessDenied('change-role', $user, [
                    'reason' => 'User vinculado a professional',
                    'attempted_roles' => $request->roles,
                ]);
            } else {
                // User normal - pode mudar roles livremente
                $user->syncRoles($request->roles);
            }

            // Track new roles after changes
            $newRoles = $user->roles->pluck('name')->toArray();

            // Log role changes
            if ($oldRoles != $newRoles) {
                $removedRoles = array_diff($oldRoles, $newRoles);
                $addedRoles = array_diff($newRoles, $oldRoles);

                foreach ($removedRoles as $roleName) {
                    $this->userLogger->roleRemoved($user, $roleName, ['source' => 'controller']);
                }

                foreach ($addedRoles as $roleName) {
                    $this->userLogger->roleAssigned($user, $roleName, ['source' => 'controller']);
                }
            }

            // Track what changed
            $changes = [];
            $newData = $user->only(['name', 'email', 'phone', 'postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'allow', 'type']);
            foreach ($newData as $key => $value) {
                if ($originalData[$key] != $value) {
                    $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
                }
            }

            // Log successful update (UserObserver will also log at model level)
            $this->userLogger->updated($user, $changes, [
                'source' => 'controller',
                'roles_changed' => !empty($removedRoles) || !empty($addedRoles),
            ]);

            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('users.edit', $user->id);
        } catch (Exception $e) {
            DB::rollBack();

            $this->userLogger->operationFailed('update', $e, [
                'user_id' => $user->id,
            ]);

            flash(self::MSG_UPDATE_ERROR)->warning();

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);

        try {
            $roles = Role::all();

            $this->userLogger->viewed($user, 'details');

            return view('users.show', compact('user', 'roles'));
        } catch (Exception $e) {
            $this->userLogger->operationFailed('show', $e, ['user_id' => $id]);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = SpatieRole::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['type'] = (! isset($request->type)) ? User::TYPE_I : $data['type'];

            $temporaryPassword = Str::random(10); // Gera a senha temporária
            $hashedPassword = bcrypt($temporaryPassword); // Gera o hash da senha

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
                'password' => $hashedPassword,
                'role_id' => 3,
                'created_by' => auth()->user()->id,
                'allow' => (bool) isset($request->allow),
                'type' => $data['type'],
            ];

            $user = new User($userData);
            $user->temporaryPassword = $temporaryPassword;
            $user->save();

            $user->syncRoles($request->roles);

            // Log role assignment
            foreach ($request->roles as $roleName) {
                $this->userLogger->roleAssigned($user, $roleName, [
                    'source' => 'controller',
                    'on_creation' => true,
                ]);
            }

            // Log user creation with additional context
            $this->userLogger->created($user, [
                'source' => 'controller',
                'roles' => $request->roles,
            ]);

            DB::commit();

            flash(self::MSG_CREATE_SUCCESS)->success();

            return redirect()->route('users.index');
        } catch (Exception $e) {
            DB::rollBack();

            $this->userLogger->operationFailed('store', $e);

            flash(self::MSG_CREATE_ERROR)->warning();

            return redirect()->back();
        }
    }



    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        DB::beginTransaction();
        try {
            // Verifica se o usuário autenticado está tentando excluir a si mesmo
            if (auth()->id() == $user->id) {
                $this->userLogger->accessDenied('delete-self', $user, [
                    'reason' => 'Usuário tentou excluir a si mesmo',
                ]);

                // Lança uma exceção para bloquear a operação
                throw new \Exception(self::MSG_DELETE_USER_SELF);
            }

            // Verifica se o user está vinculado a um professional
            $professional = $user->professional->first();
            if ($professional) {
                // Verifica se o professional tem kids vinculados
                if ($professional->kids()->count() > 0) {
                    throw new \Exception('Não é possível mover para lixeira. Este usuário está vinculado a um profissional que possui crianças atendidas.');
                }

                // Move o professional para lixeira primeiro
                $professional->deleted_by = auth()->id();
                $professional->save();
                $professional->delete();

                $this->userLogger->professionalUnlinked($user, $professional->id, [
                    'reason' => 'Professional movido para lixeira junto com user',
                ]);
            }

            // Desativa o usuário antes de enviar para lixeira
            $user->allow = false;
            $user->deleted_by = auth()->id();
            $user->save();

            // Envia para lixeira (soft delete)
            $user->delete();

            // UserObserver will log at model level
            $this->userLogger->deleted($user, [
                'source' => 'controller',
                'professional_also_deleted' => !is_null($professional),
            ]);

            DB::commit();

            // Exibe a mensagem de sucesso
            $successMessage = 'Usuário movido para a lixeira e desativado com sucesso.';
            if ($professional) {
                $successMessage .= ' O profissional vinculado também foi movido para a lixeira.';
            }
            flash($successMessage)->success();

            // Redireciona para a lista de usuários
            return redirect()->route('users.index');
        } catch (Exception $e) {
            DB::rollBack();

            $this->userLogger->operationFailed('destroy', $e, [
                'user_id' => $user->id,
            ]);

            // Exibe uma mensagem de erro ao usuário
            flash($e->getMessage())->warning();

            // Redireciona de volta
            return redirect()->back();
        }
    }

    public function trash()
    {
        $this->authorize('viewTrash', User::class);

        $users = User::onlyTrashed()
            ->with('roles')
            ->orderBy('deleted_at', 'desc')
            ->paginate(self::PAGINATION_DEFAULT);

        $this->userLogger->trashViewed([
            'total_trashed' => $users->total(),
        ]);

        return view('users.trash', compact('users'));
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            $this->authorize('restore', $user);

            // Verifica se o user tem professional vinculado na lixeira
            $professional = $user->professional()->onlyTrashed()->first();

            // Restaura o usuário da lixeira
            $user->restore();

            // Reativa o usuário
            $user->allow = true;
            $user->save();

            // Restaura o professional vinculado se existir
            if ($professional) {
                $professional->restore();

                $this->userLogger->professionalLinked($user, $professional->id, [
                    'reason' => 'Professional restaurado junto com user',
                ]);
            }

            // UserObserver will log at model level
            $this->userLogger->restored($user, [
                'source' => 'controller',
                'professional_also_restored' => !is_null($professional),
            ]);

            DB::commit();

            $successMessage = 'Usuário restaurado e reativado com sucesso.';
            if ($professional) {
                $successMessage .= ' O profissional vinculado também foi restaurado.';
            }
            flash($successMessage)->success();

            return redirect()->route('users.trash');
        } catch (Exception $e) {
            DB::rollBack();

            $this->userLogger->operationFailed('restore', $e, [
                'user_id' => $id,
            ]);

            flash('Erro ao restaurar usuário: ' . $e->getMessage())->warning();

            return redirect()->back();
        }
    }

    public function pdf($id)
    {
        try {
            $user = User::findOrFail($id);

            $pdf = PDF::loadView('users.show', compact('user'));

            $this->userLogger->viewed($user, 'pdf');

            return $pdf->download('user.pdf');
        } catch (Exception $e) {
            $this->userLogger->operationFailed('pdf', $e, [
                'user_id' => $id,
            ]);

            flash(self::MSG_DELETE_ERROR)->warning();

            return redirect()->back();
        }
    }
}
