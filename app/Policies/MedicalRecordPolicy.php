<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Listar prontuários médicos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('medical-record-list') || $user->can('medical-record-list-all');
    }

    /**
     * Visualizar um prontuário específico.
     * Profissionais podem ver apenas prontuários que criaram.
     * Quando admin cria para um profissional, created_by é setado para o user_id do profissional.
     */
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin sees everything
        if ($user->can('medical-record-show-all')) {
            return true;
        }

        // Professional can view ONLY if they created it
        if ($user->can('medical-record-show')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Criar novos prontuários.
     */
    public function create(User $user): bool
    {
        return $user->can('medical-record-create') || $user->can('medical-record-create-all');
    }

    /**
     * Determine whether the user can create a new version of the medical record.
     * Only the current version can have a new version created from it.
     * Only the creator of the original record can create new versions.
     */
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can edit everything
        if ($user->can('medical-record-edit-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-edit')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Enviar prontuário para a lixeira (soft delete).
     * Profissionais só podem deletar prontuários que ELES criaram.
     */
    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can delete everything
        if ($user->can('medical-record-delete-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-delete')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Visualizar a lixeira de prontuários.
     */
    public function viewTrash(User $user): bool
    {
        // Only admin can view trash
        return $user->can('medical-record-list-all');
    }

    /**
     * Restaurar um prontuário.
     */
    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can restore everything
        if ($user->can('medical-record-edit-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-edit')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->can('medical-record-delete-all');
    }
}

