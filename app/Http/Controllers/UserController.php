<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Professional;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct() {}

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

        $users = $query->paginate(5);

        return view('users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        try {
            $roles = SpatieRole::orderBy('name')->get();

            $message = label_case('Edit User ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return view('users.edit', compact('user', 'roles'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();

            $message = label_case('Edit User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

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
                Log::info('Tentativa de alterar role de user profissional bloqueada.', [
                    'user_id' => $user->id,
                    'attempted_roles' => $request->roles,
                ]);
            } else {
                // User normal - pode mudar roles livremente
                $user->syncRoles($request->roles);
            }

            DB::commit();

            flash(self::MSG_UPDATE_SUCCESS)->success();

            $message = label_case('Update User ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::notice($message);

            return redirect()->route('users.edit', $user->id);
        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            flash(self::MSG_UPDATE_ERROR)->warning();

            $message = label_case('Update User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);

        try {
            $roles = Role::all();

            $message = label_case('Show User ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return view('users.show', compact('user', 'roles'));
        } catch (Exception $e) {
            $message = self::MSG_NOT_FOUND;

            flash($message)->warning();

            $message = label_case('Show User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = SpatieRole::orderBy('name')->get();

        $message = label_case('Create User ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

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

            // Se for profissional, criar registro na tabela professionals
            /*
            if ($role->name === 'professional') {
                Professional::create([
                    'specialty_id' => 1, // ID padrão, pode ser ajustado conforme necessidade
                    'registration_number' => 'Pendente',
                    'created_by' => auth()->id(),
                ])->user()->attach($user->id);
            }*/

            DB::commit();

            flash(self::MSG_CREATE_SUCCESS)->success();
            Log::notice('Usuário criado com sucesso. ID: ' . $user->id . ' Email: ' . $user->email);

            return redirect()->route('users.index');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
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
                $message = label_case('Attempted to delete self. ' . self::MSG_DELETE_USER_SELF) . ' | User: ' . auth()->user()->name . ' (ID: ' . auth()->id() . ')';
                Log::alert($message);

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

                Log::notice('Professional vinculado também movido para lixeira.', [
                    'professional_id' => $professional->id,
                    'user_id' => $user->id,
                ]);
            }

            // Desativa o usuário antes de enviar para lixeira
            $user->allow = false;
            $user->deleted_by = auth()->id();
            $user->save();

            // Envia para lixeira (soft delete)
            $user->delete();

            DB::commit();

            // Exibe a mensagem de sucesso
            $successMessage = 'Usuário movido para a lixeira e desativado com sucesso.';
            if ($professional) {
                $successMessage .= ' O profissional vinculado também foi movido para a lixeira.';
            }
            flash($successMessage)->success();

            // Registra a ação de exclusão no log
            $message = label_case('User moved to trash and deactivated. ') . ' | Deleted User: ' . $user->name . ' (ID: ' . $user->id . ')';
            Log::notice($message);

            // Redireciona para a lista de usuários
            return redirect()->route('users.index');
        } catch (Exception $e) {
            DB::rollBack();

            // Exibe uma mensagem de erro ao usuário
            flash($e->getMessage())->warning();

            // Registra o erro no log
            $message = label_case('Error while deleting user: ' . $e->getMessage()) . ' | User: ' . auth()->user()->name . ' (ID: ' . auth()->id() . ')';
            Log::error($message);

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
            ->paginate(15);

        $message = label_case('View Trash Users') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

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

                Log::notice('Professional vinculado também restaurado.', [
                    'professional_id' => $professional->id,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();

            $successMessage = 'Usuário restaurado e reativado com sucesso.';
            if ($professional) {
                $successMessage .= ' O profissional vinculado também foi restaurado.';
            }
            flash($successMessage)->success();

            $message = label_case('User restored and reactivated. ') . ' | Restored User: ' . $user->name . ' (ID: ' . $user->id . ')';
            Log::notice($message);

            return redirect()->route('users.trash');
        } catch (Exception $e) {
            DB::rollBack();

            flash('Erro ao restaurar usuário: ' . $e->getMessage())->warning();

            $message = label_case('Error while restoring user: ' . $e->getMessage()) . ' | User: ' . auth()->user()->name . ' (ID: ' . auth()->id() . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function pdf($id)
    {
        try {
            $user = User::findOrFail($id);

            $pdf = PDF::loadView('users.show', compact('user'));

            $message = label_case('PDF Users Teste ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return $pdf->download('user.pdf');
        } catch (Exception $e) {
            flash(self::MSG_DELETE_ERROR)->warning();

            $message = label_case('Destroy Users ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
