<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\KidRepositoryInterface;
use App\Models\Kid;
use App\Models\User;
use App\ValueObjects\KidData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class KidService
{
    public function __construct(
        private readonly KidRepositoryInterface $kidRepository
    ) {
    }

    public function getAllKidsForUser(): Collection
    {
        return $this->kidRepository->getKidsForUser();
    }

    public function getPaginatedKidsForUser(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->kidRepository->paginateForUser($perPage, $filters);
    }

    public function findKidById(int $id): ?Kid
    {
        return $this->kidRepository->find($id);
    }

    public function getAllKids(): Collection
    {
        return $this->getAllKidsForUser();
    }

    public function getKidById(int $id): ?Kid
    {
        return $this->findKidById($id);
    }

    public function getKidsByResponsible(int $responsibleId): Collection
    {
        return $this->kidRepository->findByResponsible($responsibleId);
    }

    public function getKidsByProfessional(int $professionalId): Collection
    {
        return $this->kidRepository->findByProfessional($professionalId);
    }

    public function createKid(array $data): Kid
    {
        DB::beginTransaction();

        try {
            $kidData = KidData::fromArray($data);
            $kidArray = array_merge(
                $kidData->toCreateArray(),
                ['created_by' => Auth::id() ?? 1]
            );

            if (isset($data['primary_professional'])) {
                $kidArray['primary_professional'] = $data['primary_professional'];
            }

            $kid = $this->kidRepository->create($kidArray);

            if (Auth::user()?->can('attach-to-kids-as-professional')) {
                $this->attachCurrentProfessionalToKid($kid->id);
            }

            if (isset($data['professionals'])) {
                $this->syncProfessionalsForKid($kid->id, $data['professionals'], $data['primary_professional'] ?? null);
            }

            Log::info('Kid created successfully', [
                'kid_id' => $kid->id,
                'created_by' => Auth::id() ?? 1,
            ]);

            DB::commit();

            return $kid;
        } catch (\App\Exceptions\ValueObjects\ValidationException $e) {
            DB::rollBack();
            Log::error('Error creating kid', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new \App\Exceptions\Kid\KidCreationFailedException('Erro na validação dos dados: ' . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            DB::rollBack();
            Log::error('Error creating kid', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new \App\Exceptions\Kid\KidCreationFailedException('Formato de data inválido: ' . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating kid', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new \App\Exceptions\Kid\KidCreationFailedException('Erro inesperado ao criar criança: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function updateKid(int $kidId, array $data): Kid
    {
        DB::beginTransaction();

        try {
            $kidData = KidData::fromArray($data);
            $updateArray = array_merge(
                $kidData->toArray(),
                [
                    'updated_by' => Auth::id() ?? 1,
                    'months' => $kidData->calculateAgeInMonths(),
                ]
            );
            
            if (isset($data['primary_professional'])) {
                $updateArray['primary_professional'] = $data['primary_professional'];
            }

            $result = $this->kidRepository->update($kidId, $updateArray);

            if ($result && isset($data['professionals'])) {
                $this->syncProfessionalsForKid($kidId, $data['professionals'], $data['primary_professional'] ?? null);
            }

            Log::info('Kid updated successfully', [
                'kid_id' => $kidId,
                'updated_by' => Auth::id() ?? 1,
            ]);

            DB::commit();

            return Kid::find($kidId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating kid', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new \App\Exceptions\Kid\KidUpdateFailedException('Erro ao atualizar criança: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function deleteKid(int $kidId): bool
    {
        try {
            $kid = $this->findKidById($kidId);
            
            if (!$kid) {
                throw new \App\Exceptions\Kid\KidDeletionFailedException('Criança não encontrada para exclusão');
            }

            $result = $this->kidRepository->delete($kidId);

            if (!$result) {
                throw new \App\Exceptions\Kid\KidDeletionFailedException('Falha ao excluir criança');
            }

            Log::info('Kid deleted successfully', [
                'kid_id' => $kidId,
                'deleted_by' => Auth::id() ?? 1,
            ]);

            return $result;
        } catch (\App\Exceptions\Kid\KidDeletionFailedException $e) {
            Log::error('Error deleting kid', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw $e;
        } catch (Exception $e) {
            Log::error('Error deleting kid', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new \App\Exceptions\Kid\KidDeletionFailedException('Erro inesperado ao excluir criança: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function uploadPhoto(int $kidId, $photoFile): bool
    {
        try {
            $kid = $this->findKidById($kidId);

            if (!$kid) {
                return false;
            }

            if ($kid->photo) {
                $oldPhotoPath = public_path('images/kids/' . $kid->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $path = public_path('images/kids');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = time() . '_' . $kid->id . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($path, $fileName);

            $result = $this->kidRepository->update($kidId, ['photo' => 'images/kids/' . $fileName]);

            Log::info('Kid photo uploaded successfully', [
                'kid_id' => $kidId,
                'photo_path' => 'images/kids/' . $fileName,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error uploading kid photo', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getParentsForSelect(): Collection
    {
        return \App\Models\Responsible::all();
    }

    public function getProfessionalsForSelect(): Collection
    {
        return \App\Models\Professional::with(['user', 'specialty'])
            ->whereNotNull('specialty_id')
            ->get()
            ->map(function ($professional) {
                $professional->name = $professional->user->first()?->name ?? 'Nome não encontrado';
                return $professional;
            });
    }

    private function attachCurrentProfessionalToKid(int $kidId): void
    {
        $professional = Auth::user()->professional->first();

        if ($professional) {
            $this->kidRepository->attachProfessional($kidId, $professional->id, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function syncProfessionalsForKid(int $kidId, array $professionals, ?int $primaryProfessional = null): void
    {
        $syncData = [];

        foreach ($professionals as $professionalId) {
            $syncData[$professionalId] = [
                'is_primary' => $professionalId == $primaryProfessional,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->kidRepository->syncProfessionals($kidId, $syncData);
    }
}
