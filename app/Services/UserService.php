<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\ValueObjects\UserData;
use App\ValueObjects\PasswordData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as SpatieRole;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordService $passwordService,
        private readonly UserCreationStrategyFactory $strategyFactory
    ) {
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $userData = UserData::fromArray($data);
            $passwordData = PasswordData::generate();

            // Armazenar senha temporária para o Observer acessar
            Session::put('user_creation_temp_password', $passwordData->plainPassword);

            $userArray = array_merge(
                $userData->toArray(),
                [
                    'password' => $passwordData->hashedPassword,
                    'created_by' => Auth::id(),
                ]
            );

            $user = $this->userRepository->create($userArray);

            $role = $this->assignRoleToUser($user, $data['role_id']);
            $this->handleRoleSpecificCreation($user, $role->name, $data);

            $this->notifyUserCreated($user, $passwordData->plainPassword);

            // Limpar senha da sessão
            Session::forget('user_creation_temp_password');

            return $user;
        });
    }

    public function updateUser(int $userId, array $data): User
    {
        return DB::transaction(function () use ($userId, $data) {
            $userData = UserData::fromArray($data, $userId);
            $userArray = array_merge(
                $userData->toArray(),
                ['updated_by' => Auth::id()]
            );

            $user = $this->userRepository->updateUser($userId, $userArray);

            if (isset($data['role_id'])) {
                $role = $this->assignRoleToUser($user, $data['role_id']);
                $this->handleRoleSpecificUpdate($user, $role->name, $data);
            }

            return $user;
        });
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $errors = $user->isDeletionAllowed();

            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            $this->userRepository->markAsDeleted($user, Auth::id());

            return $this->userRepository->deleteUser($user);
        });
    }

    public function findUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function getUsersForSelect(): array
    {
        return $this->userRepository->getAllForSelect();
    }

    public function getPaginatedUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($perPage, $filters);
    }

    public function getUsersByRole(string $roleName): Collection
    {
        return $this->userRepository->getByRole($roleName);
    }

    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActiveUsers();
    }

    public function getInactiveUsers(): Collection
    {
        return $this->userRepository->getInactiveUsers();
    }

    public function countUsersByRole(string $roleName): int
    {
        return $this->userRepository->countByRole($roleName);
    }

    public function getRecentUsers(int $limit = 10): Collection
    {
        return $this->userRepository->getRecentUsers($limit);
    }

    private function prepareUserData(array $data): array
    {
        $temporaryPassword = $this->passwordService->generateTemporaryPassword();
        $userModel = new User();
        $sanitizedData = $userModel->sanitizeData($data);

        return array_merge($sanitizedData, [
            'password' => Hash::make($temporaryPassword),
            'created_by' => Auth::id(),
            'temporary_password' => $temporaryPassword,
        ]);
    }

    private function prepareUserDataForUpdate(array $data): array
    {
        $userModel = new User();
        $sanitizedData = $userModel->sanitizeData($data);

        return array_merge($sanitizedData, [
            'updated_by' => Auth::id(),
        ]);
    }

    private function assignRoleToUser(User $user, int|string $roleId): SpatieRole
    {
        $role = SpatieRole::findOrFail($roleId);
        $user->syncRoles([]);
        $user->assignRole($role->name);

        return $role;
    }

    private function handleRoleSpecificCreation(User $user, string $roleName, array $data): void
    {
        $strategy = $this->strategyFactory->getStrategy($roleName);
        $strategy->createUser($user, $data);
    }

    private function handleRoleSpecificUpdate(User $user, string $roleName, array $data): void
    {
        $strategy = $this->strategyFactory->getStrategy($roleName);
        $strategy->updateUser($user, $data);
    }


    private function notifyUserCreated(User $user, string $temporaryPassword): void
    {
        Session::flash('success', 'Usuário criado com sucesso!');
        Session::flash('user_password', $temporaryPassword);
        Session::flash('user_email', $user->email);
    }

    public function getAvailableRoles(): Collection
    {
        return SpatieRole::where('name', '!=', 'superadmin')
            ->orderBy('name')
            ->get();
    }

    public function generateUserPdf(User $user): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('users.show', [
            'user' => $user,
            'roles' => $this->getAvailableRoles(),
        ]);

        return $pdf->download("user-{$user->id}.pdf");
    }

    public function sanitizeUserDataForLog(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => \Illuminate\Support\Str::mask($user->email, '*', 3),
            'roles' => $user->roles->pluck('name')->toArray(),
            'allow' => $user->allow,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function sanitizeRequestDataForLog(array $data): array
    {
        $sanitized = $data;

        // Remove senhas e dados sensíveis dos logs
        unset($sanitized['password'], $sanitized['password_confirmation']);


        // Mascarar email se presente
        if (isset($sanitized['email'])) {
            $sanitized['email'] = \Illuminate\Support\Str::mask($sanitized['email'], '*', 3);
        }

        // Mascarar telefone se presente
        if (isset($sanitized['phone']) && $sanitized['phone']) {
            $sanitized['phone'] = \Illuminate\Support\Str::mask($sanitized['phone'], '*', -4, 4);
        }

        return $sanitized;
    }
}
