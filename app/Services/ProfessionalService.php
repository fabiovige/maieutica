<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProfessionalRepositoryInterface;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ProfessionalService
{
    public function __construct(
        private readonly ProfessionalRepositoryInterface $professionalRepository
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
        return $this->professionalRepository->find($id);
    }

    public function createProfessional(array $data): Professional
    {
        DB::beginTransaction();

        try {
            $user = $this->createUserForProfessional($data);
            $professional = $this->createProfessionalRecord($data);
            $this->linkUserToProfessional($professional, $user);
            $this->sendWelcomeNotification($user);

            DB::commit();
            return $professional;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar profissional: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProfessional(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $professional = $this->professionalRepository->find($id);
            if (!$professional) {
                throw new Exception('Profissional não encontrado');
            }

            $user = $professional->user->first();
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }

            $this->updateUserData($user, $data);
            $this->updateProfessionalData($professional, $data);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar profissional: ' . $e->getMessage());
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

    private function createUserForProfessional(array $data): User
    {
        $password = Str::random(10);
        
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($password),
            'allow' => $data['allow'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    private function createProfessionalRecord(array $data): Professional
    {
        return $this->professionalRepository->create([
            'specialty_id' => $data['specialty_id'],
            'registration_number' => $data['registration_number'],
            'bio' => $data['bio'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    private function linkUserToProfessional(Professional $professional, User $user): void
    {
        $user->assignRole('professional');
        $this->professionalRepository->attachUser($professional->id, $user->id);
    }

    private function sendWelcomeNotification(User $user): void
    {
        $password = Str::random(10);
        $user->update(['password' => bcrypt($password)]);
        $user->notify(new WelcomeNotification($user, $password));
    }

    private function updateUserData(User $user, array $data): void
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'allow' => $data['allow'] ?? $user->allow,
            'updated_by' => Auth::id(),
        ]);
    }

    private function updateProfessionalData(Professional $professional, array $data): void
    {
        $professional->update([
            'specialty_id' => $data['specialty_id'],
            'registration_number' => $data['registration_number'],
            'bio' => $data['bio'] ?? $professional->bio,
            'updated_by' => Auth::id(),
        ]);
    }

    private function changeUserStatus(int $professionalId, bool $status): bool
    {
        DB::beginTransaction();

        try {
            $professional = $this->professionalRepository->find($professionalId);
            if (!$professional) {
                throw new Exception('Profissional não encontrado');
            }

            $user = $professional->user->first();
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }

            $user->update([
                'allow' => $status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao alterar status do profissional: ' . $e->getMessage());
            throw $e;
        }
    }
}