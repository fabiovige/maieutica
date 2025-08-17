<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\KidRepositoryInterface;
use App\Models\Kid;
use App\Models\User;
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
    ) {}

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

    public function createKid(array $data): Kid
    {
        DB::beginTransaction();
        
        try {
            $kidData = [
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'ethnicity' => $data['ethnicity'],
                'responsible_id' => $data['responsible_id'],
                'created_by' => Auth::id(),
            ];

            $kid = $this->kidRepository->create($kidData);

            if (Auth::user()->hasRole('professional')) {
                $this->attachCurrentProfessionalToKid($kid->id);
            }

            Log::info('Kid created successfully', [
                'kid_id' => $kid->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return $kid;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating kid', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }

    public function updateKid(int $kidId, array $data): bool
    {
        DB::beginTransaction();
        
        try {
            $updateData = [
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'ethnicity' => $data['ethnicity'],
                'responsible_id' => $data['responsible_id'] ?? null,
            ];

            $result = $this->kidRepository->update($kidId, $updateData);

            if ($result && isset($data['professionals'])) {
                $this->syncProfessionalsForKid($kidId, $data['professionals'], $data['primary_professional'] ?? null);
            }

            Log::info('Kid updated successfully', [
                'kid_id' => $kidId,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating kid', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }

    public function deleteKid(int $kidId): bool
    {
        try {
            $result = $this->kidRepository->delete($kidId);

            Log::info('Kid deleted successfully', [
                'kid_id' => $kidId,
                'deleted_by' => Auth::id(),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error deleting kid', [
                'kid_id' => $kidId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            throw $e;
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
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'pais');
        })->get();
    }

    public function getProfessionalsForSelect(): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'professional');
        })->with(['professional.specialty'])
        ->whereHas('professional', function ($query) {
            $query->whereNotNull('specialty_id');
        })->get();
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

    private function syncProfessionalsForKid(int $kidId, array $professionals, ?string $primaryProfessional = null): void
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