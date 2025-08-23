<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Services\UserCreationStrategyFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            $userData = $this->prepareUserData($data);
            $user = $this->userRepository->create($userData);

            $role = $this->assignRoleToUser($user, $data['role_id']);
            $this->handleRoleSpecificCreation($user, $role->name, $data);

            return $user;
        });
    }

    public function updateUser(int $userId, array $data): User
    {
        return DB::transaction(function () use ($userId, $data) {
            $userData = $this->prepareUserDataForUpdate($data);
            $user = $this->userRepository->updateUser($userId, $userData);

            if (isset($data['role_id'])) {
                $role = $this->assignRoleToUser($user, $data['role_id']);
                $this->handleRoleSpecificCreation($user, $role->name, $data);
            }

            return $user;
        });
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $this->validateUserDeletion($user);
            
            $this->userRepository->markAsDeleted($user, auth()->id());
            
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
        
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'postal_code' => $data['cep'] ?? null,
            'street' => $data['logradouro'] ?? null,
            'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['cidade'] ?? null,
            'state' => $data['estado'] ?? null,
            'password' => Hash::make($temporaryPassword),
            'created_by' => auth()->id(),
            'allow' => (bool) ($data['allow'] ?? false),
            'type' => $data['type'] ?? User::TYPE_I,
        ];
    }

    private function prepareUserDataForUpdate(array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'postal_code' => $data['cep'] ?? null,
            'street' => $data['logradouro'] ?? null,
            'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['cidade'] ?? null,
            'state' => $data['estado'] ?? null,
            'updated_by' => auth()->id(),
            'allow' => (bool) ($data['allow'] ?? false),
            'type' => $data['type'] ?? User::TYPE_I,
        ];
    }

    private function assignRoleToUser(User $user, int $roleId): SpatieRole
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

    private function validateUserDeletion(User $user): void
    {
        if (auth()->id() === $user->id) {
            throw new \Exception('Não é possível excluir seu próprio usuário');
        }

        if ($this->userRepository->hasRoles($user)) {
            throw new \Exception('Não é possível excluir usuário com perfis atribuídos');
        }
    }
}