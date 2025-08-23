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

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = SpatieRole::where('name', '!=', 'superadmin')->get();

        $message = label_case('Create User ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        try {
            $message = label_case('Store User ' . self::MSG_CREATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $this->userService->createUser($request->validated());

            flash(self::MSG_CREATE_SUCCESS)->success();

            return redirect()->route('users.index');
        } catch (Exception $e) {
            flash(self::MSG_CREATE_ERROR)->warning();
            $message = label_case('Store User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $message = label_case('Update User ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $this->userService->updateUser($user->id, $request->validated());

            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('users.edit', $user->id);
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $message = label_case('Destroy User ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $this->userService->deleteUser($user);

            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('users.index');
        } catch (Exception $e) {
            flash($e->getMessage())->warning();
            $message = label_case('Destroy User ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
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
