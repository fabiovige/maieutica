<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role as SpatieRole;

class UserController extends BaseController
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    public function index(Request $request): mixed
    {
        $this->authorize('view users');

        return $this->handleIndexRequest(
            $request,
            fn ($filters) => $this->userService->getPaginatedUsers($filters['per_page'], $filters),
            'users.index'
        );
    }

    public function edit(User $user): View|RedirectResponse
    {
        $this->authorize('update', $user);

        return $this->handleViewRequest(
            fn() => [
                'user' => $user,
                'roles' => SpatieRole::where('name', '!=', 'superadmin')->get()
            ],
            'users.edit',
            [],
            'Erro ao carregar dados do usuário',
            'users.index'
        );
    }

    public function show(User $user): View|RedirectResponse
    {
        $this->authorize('view', User::class);

        try {
            $roles = Role::all();

            $message = label_case('Show User ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return view('users.show', compact('user', 'roles'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();

            $message = label_case('Show User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', User::class);

        return $this->handleCreateRequest(
            fn() => [
                'roles' => SpatieRole::where('name', '!=', 'superadmin')->get()
            ],
            'users.create',
            'Create User',
            'Erro ao carregar dados de criação de usuário',
            'users.index'
        );
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        return $this->handleStoreRequest(
            fn() => $this->userService->createUser($request->validated()),
            self::MSG_CREATE_SUCCESS,
            self::MSG_CREATE_ERROR,
            'users.index',
            'Store User'
        );
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        return $this->handleUpdateRequest(
            fn() => $this->userService->updateUser($user->id, $request->validated()),
            self::MSG_UPDATE_SUCCESS,
            self::MSG_UPDATE_ERROR,
            "users.edit",
            $user->id
        );
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        return $this->handleDestroyRequest(
            fn() => $this->userService->deleteUser($user),
            self::MSG_DELETE_SUCCESS,
            'users.index',
            'Destroy User'
        );
    }

    public function pdf(User $user)
    {
        try {
            $pdf = PDF::loadView('users.show', compact('user'));

            $message = label_case('PDF Users ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            return $pdf->download("user-{$user->id}.pdf");
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();

            $message = label_case('PDF User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }
}
