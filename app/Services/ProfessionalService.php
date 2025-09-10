<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProfessionalRepositoryInterface;
use App\Enums\LogOperation;
use App\Exceptions\Professional\ProfessionalNotFoundException;
use App\Exceptions\Professional\UserAssociationNotFoundException;
use App\Exceptions\Professional\ProfessionalCreationException;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Services\Log\LoggingService;
use App\ValueObjects\ProfessionalData;
use App\ValueObjects\ProfessionalUpdateData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ProfessionalService
{
    public function __construct(
        private readonly ProfessionalRepositoryInterface $professionalRepository,
        private readonly UserService $userService,
        private readonly NotificationService $notificationService,
        private readonly PasswordService $passwordService,
        private readonly LoggingService $loggingService
    ) {
    }

    public function getAllProfessionals(): Collection
    {
        return $this->professionalRepository->all();
    }

    public function getPaginatedProfessionals(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->professionalRepository->paginateWithFilters($perPage, $filters);
    }

    public function findProfessionalById(int $id): ?Professional
    {
        return $this->professionalRepository->findWith($id, ['user', 'specialty']);
    }

    public function createProfessional(array $data): Professional
    {
        $traceId = $this->loggingService->startTrace();

        $this->loggingService->logProfessionalOperation(
            LogOperation::CREATE,
            'Starting professional creation process',
            ['registration_number' => $data['registration_number'] ?? null]
        );

        DB::beginTransaction();

        try {
            $professionalData = ProfessionalData::fromArray($data);
            $temporaryPassword = $this->passwordService->generateTemporaryPassword();

            $userData = array_merge(
                $professionalData->toUserArray(),
                [
                    'password' => Hash::make($temporaryPassword),
                    'created_by' => Auth::id(),
                ]
            );

            $user = User::create($userData);
            $user->assignRole('professional');

            $this->loggingService->logUserOperation(
                LogOperation::CREATE,
                'User account created for professional',
                ['user_id' => $user->id],
                'info'
            );

            $professionalRecord = $this->createProfessionalRecord($professionalData);
            $this->linkUserToProfessional($professionalRecord, $user);

            $this->notificationService->sendWelcomeNotification($user, $temporaryPassword);

            DB::commit();

            $this->loggingService->logProfessionalOperation(
                LogOperation::CREATE,
                'Professional creation completed successfully',
                [
                    'professional_id' => $professionalRecord->id,
                    'user_id' => $user->id,
                ],
                'info'
            );

            $professionalRecord->load('user', 'specialty');

            return $professionalRecord;
        } catch (Exception $e) {
            DB::rollBack();

            $this->loggingService->logException(
                $e,
                'Failed to create professional',
                ['trace_id' => $traceId]
            );

            throw new ProfessionalCreationException($e->getMessage(), $e);
        }
    }

    public function updateProfessional(int $id, array $data): bool
    {
        $traceId = $this->loggingService->startTrace();

        $this->loggingService->logProfessionalOperation(
            LogOperation::UPDATE,
            'Starting professional update process',
            ['professional_id' => $id]
        );

        DB::beginTransaction();

        try {
            $professional = $this->getProfessionalWithUser($id);
            $professionalUpdateData = ProfessionalUpdateData::fromArray($data, $id);

            $user = $professional->user->first();
            if (!$user) {
                throw new UserAssociationNotFoundException($id);
            }

            $this->updateUserData($user, $professionalUpdateData);
            $this->updateProfessionalData($professional, $professionalUpdateData);

            DB::commit();

            $this->loggingService->logProfessionalOperation(
                LogOperation::UPDATE,
                'Professional update completed successfully',
                [
                    'professional_id' => $id,
                    'user_id' => $user->id,
                ],
                'info'
            );

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            $this->loggingService->logException(
                $e,
                'Failed to update professional',
                [
                    'professional_id' => $id,
                    'trace_id' => $traceId,
                ]
            );

            throw $e;
        }
    }

    public function activateProfessional(int $id): bool
    {
        return $this->changeUserStatus($id, true);
    }

    public function deactivateProfessional(int $id): bool
    {
        return $this->changeUserStatus($id, false);
    }

    public function getSpecialtiesForSelect(): Collection
    {
        return Specialty::orderBy('name')->get();
    }

    public function searchProfessionals(string $term): Collection
    {
        return $this->professionalRepository->findBy('registration_number', $term);
    }

    public function findBySpecialty(int $specialtyId): Collection
    {
        return $this->professionalRepository->findBySpecialty($specialtyId);
    }

    public function getActiveProfessionals(): Collection
    {
        return $this->professionalRepository->getActiveProfessionals();
    }

    private function getProfessionalWithUser(int $id): Professional
    {
        $professional = $this->professionalRepository->find($id);

        if (!$professional) {
            throw new ProfessionalNotFoundException($id);
        }

        $professional->load('user', 'specialty');

        return $professional;
    }

    private function createProfessionalRecord(ProfessionalData $data): Professional
    {
        $professionalArray = array_merge(
            $data->toProfessionalArray(),
            ['created_by' => Auth::id()]
        );

        return $this->professionalRepository->create($professionalArray);
    }

    private function linkUserToProfessional(Professional $professional, User $user): void
    {
        $this->professionalRepository->attachUser($professional->id, $user->id);
    }

    private function getProfessionalRoleId(): int
    {
        $role = \Spatie\Permission\Models\Role::where('name', 'professional')->first();

        if (!$role) {
            throw new Exception('Role professional não encontrada');
        }

        return $role->id;
    }

    private function updateUserData(User $user, ProfessionalUpdateData $data): void
    {
        $userData = array_merge(
            $data->toUserArray(),
            ['updated_by' => Auth::id()]
        );

        $user->update($userData);
    }

    private function updateProfessionalData(Professional $professional, ProfessionalUpdateData $data): void
    {
        $professionalArray = array_merge(
            $data->toProfessionalArray(),
            ['updated_by' => Auth::id()]
        );

        $professional->update($professionalArray);
    }

    private function changeUserStatus(int $professionalId, bool $status): bool
    {
        $traceId = $this->loggingService->startTrace();
        $operation = $status ? 'activate' : 'deactivate';

        $this->loggingService->logProfessionalOperation(
            LogOperation::UPDATE,
            "Starting professional {$operation} process",
            ['professional_id' => $professionalId],
            'info'
        );

        DB::beginTransaction();

        try {
            $professional = $this->getProfessionalWithUser($professionalId);

            $user = $professional->user->first();
            if (!$user) {
                throw new UserAssociationNotFoundException($professionalId);
            }

            $user->update([
                'allow' => $status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            $this->loggingService->logSecurityEvent(
                LogOperation::UPDATE,
                "Professional account {$operation}d successfully",
                [
                    'professional_id' => $professionalId,
                    'user_id' => $user->id,
                    'changed_by' => Auth::id(),
                ],
                'warning'
            );

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            $this->loggingService->logException(
                $e,
                "Failed to {$operation} professional",
                [
                    'professional_id' => $professionalId,
                    'trace_id' => $traceId,
                ]
            );

            throw $e;
        }
    }
}
