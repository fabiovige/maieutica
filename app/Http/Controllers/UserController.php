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

class UserController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $this->authorize('view users');

        $users = User::query()
            ->when(! auth()->user()->hasRole('superadmin'), function ($query) {
                $query->where('name', '!=', 'Super Admin');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        try {

            $roles = SpatieRole::where('name', '!=', 'superadmin')->get();

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

    public function show($id)
    {
        $this->authorize('view', User::class);
        try {
            $user = User::findOrFail($id);
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

        $roles = SpatieRole::where('name', '!=', 'superadmin')->get();

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

            // Gera uma senha aleatória
            $plainPassword = Str::random(8);

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
                'password' => Hash::make($plainPassword),
                'passwordView' => $plainPassword,
                'role_id' => 3,
                'created_by' => auth()->user()->id,
                'allow' => (bool) isset($request->allow),
                'type' => $data['type'],
            ];

            $user = User::create($userData);

            // Envia o email de boas-vindas
            $notification = new WelcomeNotification($user, $plainPassword);
            $user->notify($notification);

            // Atribui o papel (role)
            $role = SpatieRole::find($data['role_id']);
            $user->syncRoles([]);
            $user->assignRole($role->name);

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

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['type'] = (! isset($data['type'])) ? User::TYPE_I : $data['type'];

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

            $message = label_case('Update User ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::notice($message);

            return redirect()->route('users.edit', $id);
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_UPDATE_ERROR)->warning();

            $message = label_case('Update User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

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

            // Verifica se o usuário tem papéis atribuídos
            if ($user->roles()->count() > 0) {
                $message = label_case('Attempted to delete user with roles. ' . self::MSG_DELETE_USER_WITH_ROLE) . ' | User: ' . $user->name . ' (ID: ' . $user->id . ')';
                Log::alert($message);

                // Lança uma exceção para impedir a exclusão de usuários com papéis
                throw new Exception(self::MSG_DELETE_USER_WITH_ROLE);
            }

            // Marca o usuário como deletado por
            $user->deleted_by = auth()->id();
            $user->save(); // Usa save() em vez de update() quando há apenas uma mudança

            // Exclui o usuário
            $user->delete();

            DB::commit();

            // Exibe a mensagem de sucesso
            flash(self::MSG_DELETE_SUCCESS)->success();

            // Registra a ação de exclusão no log
            $message = label_case('User deleted successfully. ' . self::MSG_DELETE_SUCCESS) . ' | Deleted User: ' . $user->name . ' (ID: ' . $user->id . ')';
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
