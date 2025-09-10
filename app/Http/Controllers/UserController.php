<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            fn () => [
                'user' => $user,
                'roles' => $this->userService->getAvailableRoles(),
            ],
            'users.edit',
            [],
            'Erro ao carregar dados do usuário',
            'users.index'
        );
    }

    public function show(User $user): View|RedirectResponse
    {
        $this->authorize('view', $user);

        return $this->handleViewRequest(
            fn () => [
                'user' => $user,
                'roles' => $this->userService->getAvailableRoles(),
            ],
            'users.show',
            [],
            'Erro ao carregar dados do usuário',
            'users.index'
        );
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', User::class);

        return $this->handleCreateRequest(
            fn () => [
                'roles' => $this->userService->getAvailableRoles(),
            ],
            'users.create',
            [],
            'Erro ao carregar dados de criação de usuário',
            'users.index'
        );
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        return $this->handleStoreRequest(
            fn () => $this->userService->createUser($request->validated()),
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
            fn () => $this->userService->updateUser($user->id, $request->validated()),
            self::MSG_UPDATE_SUCCESS,
            self::MSG_UPDATE_ERROR,
            'users.edit',
            $user->id
        );
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->deleteUser($user);
            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('users.index');
        } catch (\Exception $e) {
            $context = array_merge($this->getCurrentUserContext(), [
                'target_user' => $this->userService->sanitizeUserDataForLog($user),
                'error' => $e->getMessage(),
            ]);
            \Illuminate\Support\Facades\Log::error('Destroy User', $context);
            flash('Erro ao excluir usuário')->error();

            return redirect()->back();
        }
    }

    public function pdf(User $user)
    {
        $this->authorize('export', $user);

        try {
            return $this->userService->generateUserPdf($user);
        } catch (\Exception $e) {
            $context = array_merge($this->getCurrentUserContext(), [
                'target_user' => $this->userService->sanitizeUserDataForLog($user),
                'error' => $e->getMessage(),
            ]);
            \Illuminate\Support\Facades\Log::error('PDF Generation Error', $context);
            flash('Erro ao gerar PDF do usuário')->error();

            return redirect()->route('users.show', $user);
        }
    }
}
