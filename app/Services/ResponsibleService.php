<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Responsible;
use App\ValueObjects\ResponsibleData;
use App\Exceptions\Responsible\ResponsibleCreationFailedException;
use App\Exceptions\Responsible\ResponsibleUpdateFailedException;
use App\Exceptions\Responsible\ResponsibleDeletionFailedException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ResponsibleService
{
    public function getAllResponsibles(): Collection
    {
        return Responsible::all();
    }

    public function getPaginatedResponsibles(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Responsible::query();

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cell', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findResponsibleById(int $id): ?Responsible
    {
        return Responsible::with(['user', 'kids'])->find($id);
    }

    public function createResponsible(array $data): Responsible
    {
        DB::beginTransaction();

        try {
            $responsibleData = ResponsibleData::fromArray($data);
            $responsibleArray = array_merge(
                $responsibleData->toCreateArray(),
                ['created_by' => Auth::id() ?? 1]
            );

            $responsible = Responsible::create($responsibleArray);

            Log::info('Responsible created successfully', [
                'responsible_id' => $responsible->id,
                'created_by' => Auth::id() ?? 1,
            ]);

            DB::commit();

            return $responsible;
        } catch (\App\Exceptions\ValueObjects\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error creating responsible', [
                'errors' => $e->getErrors(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new ResponsibleCreationFailedException($e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating responsible', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new ResponsibleCreationFailedException('Erro ao criar responsável: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updateResponsible(int $responsibleId, array $data): bool
    {
        DB::beginTransaction();

        try {
            $responsibleData = ResponsibleData::fromArray($data, $responsibleId);
            $updateArray = array_merge(
                $responsibleData->toUpdateArray(),
                ['updated_by' => Auth::id() ?? 1]
            );

            $result = Responsible::where('id', $responsibleId)->update($updateArray);

            Log::info('Responsible updated successfully', [
                'responsible_id' => $responsibleId,
                'updated_by' => Auth::id() ?? 1,
            ]);

            DB::commit();

            return $result > 0;
        } catch (\App\Exceptions\ValueObjects\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error updating responsible', [
                'responsible_id' => $responsibleId,
                'errors' => $e->getErrors(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new ResponsibleUpdateFailedException($e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating responsible', [
                'responsible_id' => $responsibleId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw new ResponsibleUpdateFailedException('Erro ao atualizar responsável: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteResponsible(int $responsibleId): bool
    {
        try {
            $responsible = $this->findResponsibleById($responsibleId);

            if (!$responsible) {
                throw new Exception('Responsável não encontrado');
            }

            // Verificar se o responsável tem crianças associadas
            if ($responsible->kids && $responsible->kids->count() > 0) {
                throw new Exception('Não é possível excluir responsável com crianças associadas');
            }

            $result = $responsible->delete();

            Log::info('Responsible deleted successfully', [
                'responsible_id' => $responsibleId,
                'deleted_by' => Auth::id() ?? 1,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error deleting responsible', [
                'responsible_id' => $responsibleId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 1,
            ]);

            throw $e;
        }
    }

    public function getResponsiblesForSelect(): array
    {
        return Responsible::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($responsible) {
                return [$responsible->id => "{$responsible->name} ({$responsible->email})"];
            })
            ->toArray();
    }

    public function searchResponsibles(string $term): Collection
    {
        return Responsible::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->limit(10)
            ->get();
    }
}
